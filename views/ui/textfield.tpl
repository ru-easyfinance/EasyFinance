{if $label}
<label class="b-custom-input-label">{$label}</label>
{/if}
<div class="b-custom-input{if !$selectbox} no-selectbox{/if}{if $class} {$class}{/if}{if $disabled} b-disabled{/if}">
    <div class="b-custom-input-border">
        {if $placeholder || $placeholder == 0}<em class="b-placeholder">{$placeholder}</em>{/if}
        <input class="b-custom-input-field"{if $id} id="{$id}"{/if}{if $name} name="{$name}"{/if} type="{if $type}{$type}{else}text{/if}"{if $disabled || $selectbox} disabled{/if} value="{$value}"{if $maxlength} maxlength={$maxlength}{/if} />
        {if $selectbox}
            <div class="b-custom-select-wrap">
                <select class="b-custom-select"{if $selectid} id="{$selectid}"{/if}>
                    {$selectbox}
                </select>
            </div>
            <div class="b-custom-select-trigger"><i></i></div>
        {/if}
    </div>
</div>
{if $hint}
<p>{$hint}</p>
{/if}