ips.templates.set('printful.store.searchedItem', ` \
	<li class='ipsAutocompleteMenu_item' data-id='{{id}}' data-value='{{{id}}}:{{{value}}}' role='option'>\
		<div class='ipsClearfix'>\
			<div class="ipsDataItem">\
                <div class="ipsDataItem_icon">\
                    <img src='{{{image}}}' alt="{{{value}}}" class="ipsThumb ipsThumb_small">\
                </div>\
                <div class="ipsDataItem_main">\
                    <div class='ipsDataItem_title ipsType_bold'>\
                        {{{value}}}\
                    </div>\
                    <div class='ipsType_small'>\
                        {{{parents}}}
                    </div>\
                </div>\
            </div>\
		</div>\
	</li>\
`);