{if isset($category_grid_items) && $category_grid_items}
<section class="cci-category-grid" id="cci-category-grid" aria-labelledby="cci-category-grid-title">
  <div class="container">
    <h2 class="section-title" id="cci-category-grid-title">
      {$cci_categorygrid_title|escape:'html':'UTF-8'}
    </h2>

    <div class="cci-category-grid-items">
      {foreach from=$category_grid_items item=item}
      {assign var=hasSubcategories value=$cci_categorygrid_show_subcategory && isset($item.sub_categories) &&
      $item.sub_categories}

      {if $hasSubcategories}
      <article class="cci-category-grid-card">
        {if $cci_categorygrid_show_image && !empty($item.image) && $item.has_image}
        <a href="{$item.link|escape:'html':'UTF-8'}" class="cci-category-grid-image" aria-hidden="true" tabindex="-1">
          <img src="{$item.image|escape:'html':'UTF-8'}" alt="{$item.name|escape:'html':'UTF-8'}" loading="lazy">
        </a>
        {/if}

        <div class="cci-category-grid-content">
          <h3 class="cci-category-grid-title">
            <a href="{$item.link|escape:'html':'UTF-8'}">
              {$item.name|escape:'html':'UTF-8'}
            </a>
          </h3>

          <ul class="cci-category-grid-subcategories"
            aria-label="{$cci_categorygrid_subcategories_label|escape:'html':'UTF-8'}">
            {foreach from=$item.sub_categories item=sub}
            <li>
              <a href="{$sub.link|escape:'html':'UTF-8'}">
                {$sub.name|escape:'html':'UTF-8'}
              </a>
            </li>
            {/foreach}
          </ul>
        </div>
      </article>
      {else}
      <a href="{$item.link|escape:'html':'UTF-8'}" class="cci-category-grid-card cci-category-grid-card-link"
        aria-label="{$item.name|escape:'html':'UTF-8'}">
        {if $cci_categorygrid_show_image && !empty($item.image) && $item.has_image}
        <div class="cci-category-grid-image" aria-hidden="true">
          <img src="{$item.image|escape:'html':'UTF-8'}" alt="{$item.name|escape:'html':'UTF-8'}" loading="lazy">
        </div>
        {/if}

        <span class="cci-category-grid-content">
          <span class="cci-category-grid-title">
            {$item.name|escape:'html':'UTF-8'}
          </span>
        </span>
      </a>
      {/if}
      {/foreach}
    </div>
  </div>
</section>
{else}
<section class="cci-category-grid">
  <div class="container">
    <div class="alert alert-info" role="alert">
      {$cci_categorygrid_empty_state|escape:'html':'UTF-8'}
    </div>
  </div>
</section>
{/if}
