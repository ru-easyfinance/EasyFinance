{if $label}
<label class="b-custom-textarea-label">{$label}</label>
{/if}
<div class="b-custom-textarea{if $class} {$class}{/if}">
    <div class="b-custom-textarea-border">
        {if $placeholder || $placeholder == 0}<em class="b-placeholder">{$placeholder}</em>{/if}
        <textarea class="b-custom-textarea-field{if $class} {$class}{/if}" rows="3" {if $id}id="{$id}"{/if} name="{if $prefix_fields}{$prefix_fields}[{/if}{$name}{if $prefix_fields}]{/if}">{$value|escape:'html'}</textarea>
    </div>
</div>