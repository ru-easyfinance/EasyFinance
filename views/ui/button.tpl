<div {if $id}id="{$id}"{/if} class="b-button{if $position} {$pos}{/if}{if $class} {$class}{/if}{if $disabled} b-disabled{/if}{if $color} {$col}{/if}">
	<span class="b-button-l"></span>
	<span class="b-button-c">
		<button {if $disabled}disabled="disabled"{/if} {if $onclick}onclick="{$onclick}"{/if}{if $rel} rel="{$rel}"{/if}{if $title} title="{$title}"{/if} type="{if $type}{$type}{else}submit{/if}" alt="{if $alt}{$alt}{elseif !$alt && $value}{$value}{else}Отправить{/if}">{if $value}{$value}{/if}</button>
	</span>
	<span class="b-button-r"></span>
	<b class="clear"></b>
</div>