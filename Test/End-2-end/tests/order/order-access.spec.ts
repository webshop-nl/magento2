/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * What installing the module leaves behind for the marketplace to authenticate with.
 *
 * The order route sits behind an access control resource, and the module creates its own
 * integration during setup to hold the token a merchant pastes into their account. This spec runs
 * before anything else in the order suite, so it describes the state the installation produced
 * rather than a state the tests arranged. The permission is granted only at the moment the
 * integration is created, so an installation that failed here leaves a token that every request is
 * refused with.
 */

import { expect, test } from '@playwright/test';
import WebshopNLApi from 'Services/WebshopNLApi';

const api = new WebshopNLApi();

test.describe('Integration the module installs for itself', () => {
  test('the shop this suite runs against has a base currency', () => {
    const currency = api.requireDb().readConfig([
      'currency/options/base',
      'currency/options/default',
      'currency/options/allow',
    ]);
    console.log(`Shop currency: ${JSON.stringify(currency)}`);

    // Not an assertion about the module. The import forces the quote's current currency to EUR,
    // so what the shop's own base currency is decides whether a converted price survives, and
    // that number belongs in the log next to every total this suite checks.
    expect(Object.keys(currency).length, 'the currency configuration is readable').toBeGreaterThan(0);
  });

  test('installing the module leaves an active integration with a token', () => {
    const state = api.integrationState();
    // Logged whichever way the assertions go: a wrong assumption about which rows matter reads as
    // a missing permission, and then the module gets blamed for the measurement.
    console.log(`Integration state: ${JSON.stringify(state)}`);

    expect(state.exists, 'the integration exists after setup:upgrade').toBe(true);
    expect(state.status, 'and is active').toBe(1);
    expect(state.hasToken, 'and has an access token').toBe(true);
  });

  test('that integration may use the order route', () => {
    const state = api.integrationState();

    expect(
      state.grantedResources,
      `granted resources: ${state.grantedResources.join(', ') || 'none'}; roles: ${JSON.stringify(state.roles)}; rules: ${JSON.stringify(state.rules)}`
    ).toContain('WebshopNL_Connect::webapi');
  });

  test("Magento's own access control allows that role on the order resource", () => {
    const verdict = api.aclVerdict('WebshopNL_Connect::webapi');
    console.log(`ACL verdict: ${JSON.stringify(verdict)}`);

    // The rule table and the request can disagree: a resource the built list does not know about
    // makes Magento fall back to the role's blanket permission, which is deny, and the refusal
    // then names the resource as if the grant were missing.
    expect(verdict.hasResource, 'the order resource is part of the access control list').toBe(true);
    expect(verdict.hasRole, `the integration's role ${verdict.roleId} is part of it too`).toBe(true);
    expect(verdict.isAllowed, `verdict for role ${verdict.roleId} on the order resource`).toBe(true);
  });

  test('with bearer tokens disallowed the route refuses the module\'s own token', async ({ baseURL }) => {
    // Magento's default since 2.4.4. Asserted rather than only worked around, because this is what
    // a merchant runs into: the module hands them a token, the marketplace posts with it, and every
    // request comes back refused naming a resource that is in fact granted.
    api.allowIntegrationBearerToken(false);
    const refused = await api.postOrder(baseURL!, { order_id: 'E2E-ACCESS-PROBE' }, 1);
    console.log(`With the setting off: ${refused.status} ${JSON.stringify(refused.body).slice(0, 160)}`);

    expect(refused.status, 'refused while the setting is off').toBe(401);

    api.allowIntegrationBearerToken(true);
  });

  test('the order route accepts the token the module mints', async ({ baseURL }) => {
    // Deliberately incomplete: what is under test is whether the request gets past authentication,
    // not whether it imports. An authorised request is answered by the module's own validation; an
    // unauthorised one never reaches it. So a 401 here is an access problem and anything else is
    // not, and no order is created either way.
    api.allowIntegrationBearerToken(true);
    const answer = await api.postOrder(baseURL!, { order_id: 'E2E-ACCESS-PROBE' }, 1);
    console.log(`With the setting on: ${answer.status} ${JSON.stringify(answer.body).slice(0, 200)}`);

    expect(answer.status, 'the route does not refuse the token the module mints').not.toBe(401);
  });

  test('a route behind one of Magento\'s own resources answers the same token', async ({ baseURL }) => {
    // The integration is granted Magento_Backend::admin as well as the module's own resource.
    // /V1/modules sits behind the first. If that answers and the module's own route does not, the
    // web tier is missing our resource rather than the whole integration being unauthenticated.
    api.allowIntegrationBearerToken(true);
    const magento = await api.restGet(baseURL!, '/V1/modules');
    const ours = await api.restGet(baseURL!, '/V1/webshopnl/info');
    console.log(`/V1/modules: ${magento.status} ${magento.body}`);
    console.log(`/V1/webshopnl/info: ${ours.status} ${ours.body}`);

    // Issued again from inside the container, past anything in front of it. A token that works
    // there and not here was never delivered to Magento in the first place.
    const inside = api.restGetInsideContainer('/V1/modules');
    console.log(`/V1/modules from inside the container: ${inside.status} ${inside.body}`);

    expect(
      { throughTheProxy: magento.status, insideTheContainer: inside.status },
      'the Authorization header survives the way in'
    ).toEqual({ throughTheProxy: 200, insideTheContainer: 200 });

    expect(
      { magento: magento.status, ours: ours.status },
      'both routes are reached with the same token'
    ).toEqual({ magento: 200, ours: 200 });
  });

  test('the grant sits on a role the Web API actually consults', () => {
    const state = api.integrationState();

    // Magento resolves the role for a token by user id together with user type, and an
    // integration is user type 1 (UserContextInterface::USER_TYPE_INTEGRATION). A rule on a role
    // of any other type is never read for a Web API request, so the token is refused while the
    // grant sits in the table looking correct.
    const consulted = (state.rules ?? []).filter(
      (rule) => rule.user_type === 1 && rule.permission === 'allow'
    );

    expect(
      consulted.map((rule) => rule.resource_id),
      `rules on an integration role: ${JSON.stringify(state.rules)}`
    ).toContain('WebshopNL_Connect::webapi');
  });
});
