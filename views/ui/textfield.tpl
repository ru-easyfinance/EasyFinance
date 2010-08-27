{if $label}
<label class="b-custom-input-label">{$label}</label>
{/if}
<div class="b-custom-input{if $class} {$class}{/if}">
    <div class="b-custom-input-border">
        {if $placeholder || $placeholder == 0}<em class="b-placeholder">{$placeholder}</em>{/if}
        <input name="{$name}" {if $id}id="{$id}"{/if} class="b-custom-input-field" type="{if $type}{$type}{else}text{/if}" value="{$value|escape:'html'}" />
        {if $selectbox}
            <select class="b-custom-select"{if $selectid} id="{$selectid}"{/if}>
                {$selectbox}
            </select>
            <div class="b-custom-select-trigger"><i></i></div>
        {/if}
    </div>
</div>