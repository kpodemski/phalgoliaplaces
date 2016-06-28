{extends file="helpers/form/form.tpl"}
{block name="after"}

<div class="panel">
	<div class="panel-heading">
		{l s='Important informations' mod='phalgoliaplaces'}
	</div>
    <p>
        {l s='This module allows your customers quickly select address based on search results from Algolia Places.' mod='phalgoliaplaces'}
    </p>
	<p>
        {l s='The Algolia Places API enforces some rate limits, currently (June 2016) this limit is 1.000 requests per day for a one domain. You can increase this limit to 100.000 by registering a new account on https://www.algolia.com/users/sign_up/places.' mod='phalgoliaplaces'}
    </p>
    <p class="text-muted">
        {l s='Module developed by %s from %s' sprintf=['<b>Krystian Podemski</b>', '<b>PrestaHome</b>']}
    </p>
</div>

{/block}