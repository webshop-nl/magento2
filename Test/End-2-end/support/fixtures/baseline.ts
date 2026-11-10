/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

import WebshopNLApi from 'Services/WebshopNLApi';

/**
 * Everything the baseline specs need to know about this module, and the only file of the baseline
 * layer this module edits.
 *
 * The specs themselves are generic on purpose: they read the module's own ui_component XML, its
 * Controller/Adminhtml directory and Magento's configuration structure, so they keep working when
 * someone adds a column, a route or a field. What differs per module is which sections exist,
 * which CLI commands there are, and what "do the module's own work once" means. Here that is
 * generating the product feed for store view 1.
 */

/**
 * The module's configuration sections, in the order the admin shows them.
 *
 * Spelled out as string literals: the sections are read from this file as text, so an imported
 * constant would read as an empty list and compare nothing. Two of the three ids are bare rather
 * than prefixed, which is how the module declares them in system.xml.
 */
export const SECTIONS = ['webshopnl_general', 'feed', 'order'];

/**
 * The first segment of every configuration path this module owns.
 *
 * Not the same thing as the section ids above, and that is the whole reason this exists: the
 * sections are `webshopnl_general`, `feed` and `order` while every path they write begins with
 * `webshopnl/`. Deriving ownership from the section ids classed all 103 fields as somebody else's
 * and the "everything on" spec then set nothing and verified nothing, while passing.
 */
export const CONFIG_PREFIXES = ['webshopnl'];

/** Every console command the module registers. */
export const CLI_COMMANDS = ['webshopnl:feed:create'];

/**
 * Arguments a command cannot run without, by command name. The feed command takes an optional
 * store id and generates every enabled store view without one, so nothing is needed here.
 */
export const CLI_ARGUMENTS: Record<string, string> = {};

/** A command as the baseline runs it: the name, plus whatever argument it cannot do without. */
export function cliInvocation(command: string): string {
  const argument = CLI_ARGUMENTS[command];

  return argument === undefined ? command : `${command} ${argument}`;
}

/**
 * The module's own error log, relative to var/log. This one writes into a subdirectory of its
 * own rather than a file at the top level.
 */
export const ERROR_LOG = 'webshopnl/error.log';

/**
 * Admin pages that must render in the browser, not just answer a request. This module ships no
 * listing of its own: its admin surface is the configuration, which the section specs cover, and
 * its two controllers both answer with a redirect rather than a page.
 */
export const ADMIN_PAGES: string[] = [];

/**
 * Bring the module to the state the baseline assumes: enabled, feed on, default file name.
 * Idempotent; each spec calls it in `beforeAll`.
 */
export async function prepareBaseline(api: WebshopNLApi): Promise<void> {
  await api.setMagentoConfig(process.env.BASE_URL || 'https://magento.test/', {
    'webshopnl/general/enable': '1',
    'webshopnl/feed/enable': '1',
    'webshopnl/feed/filename': 'webshop.ndjson',
  });
  api.flushAllCaches();
}

/** Undo whatever `prepareBaseline` created that another spec would trip over. */
export async function cleanupBaseline(api: WebshopNLApi): Promise<void> {
  void api;
}

/**
 * Run the module's real work once and report how it went: the feed for store view 1. It has to
 * report a non-zero product count, because the fixture catalogue has products; a feed that writes
 * nothing is broken.
 */
export function runModuleWork(api: WebshopNLApi): { label: string; exitCode: number; output: string; mustProduce: boolean }[] {
  const command = 'webshopnl:feed:create --store-id=1';

  return [{ label: command, ...api.runCliRaw(command), mustProduce: true }];
}

/**
 * The message a command is allowed to refuse with when the module is switched off: "Product Feed
 * for this StoreId not enabled." from the feed repository. With the feed off for every store view
 * the command prints nothing at all and exits 0, which the baseline accepts as a clean refusal.
 */
export const DISABLED_MESSAGE = /not enabled/i;
