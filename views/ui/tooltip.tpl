{if $content || $js==1}
<div class="b-tooltip">
    <div class="b-tooltip-border">
        <i class="b-tooltip-arrow"></i>
        <div class="b-tooltip-container">
            {if $content}{$content}{/if}
        </div>
    </div>
</div>
{/if}