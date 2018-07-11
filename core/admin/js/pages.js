var BigTreePages = (function() {
	var CalloutCount = 0;
	var CalloutDescription = false;
	var CalloutNumber = 0;
	var CurrentCallout = false;
	var CurrentPage;
	var CurrentPageTemplate;
	var ExternalLinkField;
	var ExternalTimer;
	var NavTitle;
	var NewWindowControl;
	var LockTimer;
	var PageTitle;
	var PageTitleDidFocus = false;
	var RedirectLowerField;
	var RedirectLowerFieldControl;
	var TemplateSelect;
	var TemplateSelectControl;
	var TemplateTimer;
	var TrunkField;

	function init(settings) {
		CurrentPage = settings.page;
		CurrentPageTemplate = settings.template;
		ExternalLinkField = $("#external_link");
		NavTitle = $("#nav_title");
		NewWindowControl = $("#new_window").get(0).customControl;
		PageTitle = $("#page_title");
		RedirectLowerField = $("#redirect_lower");
		RedirectLowerFieldControl = RedirectLowerField.get(0).customControl;
		TemplateSelect = $("#template_select");
		TemplateSelectControl = TemplateSelect.get(0).customControl;
		TrunkField = $("#trunk_field");

		RedirectLowerField.click(function() {
			if ($(this).prop("checked")) {
				TemplateSelectControl.disable();
				ExternalLinkField.prop("disabled", true);
				NewWindowControl.disable();
			} else {
				TemplateSelectControl.enable();
				ExternalLinkField.prop("disabled", false);
				NewWindowControl.enable();
			}
		});

		// Tagger
		BigTreeTagAdder.init();
		
		// Watch for changes in the template, update the Content tab.
		ExternalTimer = setInterval(checkExternal, 300);
		TemplateTimer = setInterval(checkTemplate, 300);
		
		$(".save_and_preview").click(function(ev) {
			ev.preventDefault();

			var sform = $(this).parents("form");
			sform.attr("action","admin_root/pages/update/?preview=true");
			sform.submit();
		});
		
		// Observe the Nav Title for auto filling the Page Title the first time around.
		NavTitle.keyup(function() {
			if (!PageTitle.get(0).defaultValue && !PageTitleDidFocus) {
				PageTitle.val(NavTitle.val());
			}
		});

		PageTitle.focus(function() {
			PageTitleDidFocus = true;
		});

		// Setup lock timer if we're editing a page
		if (CurrentPage) {
			LockTimer = setInterval(function() {
				$.secureAjax('<?=ADMIN_ROOT?>ajax/refresh-lock/', { 
					type: 'POST', 
					data: { 
						table: 'bigtree_pages', 
						id: CurrentPage
					}
				});
			}, 60000);
		}
	}

	function checkExternal() {
		if (ExternalLinkField.val()) {
			TemplateSelectControl.disable();
			RedirectLowerFieldControl.disable();

			if (TrunkField.length) {
				TrunkField.get(0).customControl.disable();
			}
		} else {
			RedirectLowerFieldControl.enable();

			if (TrunkField.length) {
				TrunkField.get(0).customControl.enable();
			}

			if (!RedirectLowerField.prop("checked")) {
				TemplateSelectControl.enable();
			}
		}
	}

	function checkTemplate() {
		if (TemplateSelect.length) {
			if (RedirectLowerField.prop("checked")) {
				var current_template = "!";
			} else if (ExternalLinkField.val()) {
				var current_template = "";
			} else {
				var current_template = TemplateSelect.val();
			}

			if (CurrentPageTemplate != current_template) {
				// Unload all TinyMCE fields.
				if (tinyMCE) {
					for (id in BigTree.TinyMCEFields) {
						tinyMCE.execCommand('mceFocus', false, BigTree.TinyMCEFields[id]);
						tinyMCE.execCommand("mceRemoveControl", false, BigTree.TinyMCEFields[id]);
					}
				}

				CurrentPageTemplate = current_template;

				if (CurrentPage !== false) {
					$("#template_type").load("admin_root/ajax/pages/get-template-form/", {
						page: CurrentPage, 
						template: CurrentPageTemplate 
					}, function() { 
						BigTreeCustomControls("#template_type"); 
					});
				} else {
					$("#template_type").load("admin_root/ajax/pages/get-template-form/", { 
						template: CurrentPageTemplate 
					}, function() { 
						BigTreeCustomControls("#template_type"); 
					});
				}
			}
		}
	}

	return { init: init };
})();
