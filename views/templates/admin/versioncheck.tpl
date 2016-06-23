{*
 * 2016 Michael Dekker
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@michaeldekker.com so we can send you a copy immediately.
 *
 *  @author    Michael Dekker <prestashop@michaeldekker.com>
 *  @copyright 2016 Michael Dekker
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="panel">
    <h3><i class="icon icon-refresh"></i> {l s='Check for updates' mod='phalgoliaplaces'}</h3>
    <p>
        <strong>{l s='Check if this module needs updates' mod='phalgoliaplaces'}</strong><br />
    </p>
    {if $needsUpdate}
        <div class="alert alert-warning">
            {l s='This module needs to be updated to version %s' mod='phalgoliaplaces' sprintf=[$latestVersion]}
        </div>
    {else}
        <div class="alert alert-success">
            {l s='This module is up to date.' mod='phalgoliaplaces'}
        </div>
    {/if}
    <a class="btn btn-default" href="{$baseUrl}&amp;phalgoliaplacesCheckUpdate=1"><i class="icon icon-search"></i> {l s='Check for updates' mod='phalgoliaplaces'}</a>
    {if $needsUpdate}
        <a class="btn btn-default" href="{$baseUrl}&amp;phalgoliaplacesApplyUpdate=1"><i class="icon icon-refresh"></i> {l s='Update module' mod='phalgoliaplaces'}</a>
    {/if}
</div>