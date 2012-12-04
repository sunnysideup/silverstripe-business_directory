

jQuery(document).ready(
 function(){
	//hide sections
	jQuery('.findSection').hide();

	//search form
	jQuery("input[@name='CertificationAndOr']").change(
		function() {
			jQuery(".andOr").html(jQuery("input[@name='CertificationAndOr']:checked").val());
		}
	);
	jQuery(".andOr").html("AND");
	jQuery(".andOr, #CertificationAndOr, #removeCriteria, #addRemovePipe, #ProductCategory2, #CertificationType2, #ProductCategory3, #CertificationType3").hide();
	jQuery("#addCriteria").click(
		function() {
			if (jQuery("#ProductCategory2").css('display') != 'none') {
				jQuery("#ProductCategory3, #CertificationType3, #andOr2").slideDown(400);
				jQuery("#removeCriteria,").show();
				jQuery("#addCriteria, #addRemovePipe").hide();
			} else {
				jQuery("#ProductCategory2, #CertificationType2, #andOr1, #CertificationAndOr").slideDown(400);
				jQuery("#addCriteria, #removeCriteria, #addRemovePipe").show();
			}
			return false;
		}
	);
	jQuery("#removeCriteria").click(
		function() {
			if (jQuery("#ProductCategory3").css('display') != 'none') {
				jQuery("#ProductCategory3, #CertificationType3, #andOr2").slideUp(400);
				jQuery("#ProductCategory3 option:contains(All)").attr("selected", true);
				jQuery("#CertificationType3 option:contains(All)").attr("selected", true);
				jQuery("#addCriteria, #removeCriteria, #addRemovePipe").show();
			} else {
				jQuery("#ProductCategory2, #CertificationType2, #andOr1, #CertificationAndOr").slideUp(400);
				jQuery("#ProductCategory2 option:contains(All)").attr("selected", true);
				jQuery("#CertificationType2 option:contains(All)").attr("selected", true);
				jQuery("#addCriteria").show();
				jQuery("#removeCriteria, #addRemovePipe").hide();
			}
			return false;
		}
	);

	//tabs
	jQuery('#BusinessSearchForm_SortBy #sortby').hide();
	jQuery('#BusinessSearchForm_SortBy #showhidesortby').click(
		function () {
			jQuery('#BusinessSearchForm_SortBy #sortby').slideToggle();
			return false;
		}
	);
	jQuery("#topTabListOption1").click();

	//filter form
	jQuery("#filterOptions input").click(
		function() {
			highLightFilters();
		}
	);
	//need this for our beloved IE friends
	jQuery("#filterOptions input").change(
		function() {
			highLightFilters();
		}
	);

	highLightFilters();
	//has to be after highLightFilters
	jQuery("#functionFilter .Actions").hide();
	jQuery("#filterOptions label.left").show();
	jQuery("#functionFilter .Actions").css("border", "2px solid orange");

	if (jQuery("#topTabListOption1").length == 0) {
		jQuery(".searchSection").show();
	}
	jQuery('.filterDetails').hide();
}
);


function showSection(sectionName, el) {
 jQuery(".highlightSearchOption").removeClass("highlightSearchOption");
 jQuery('.findSection').fadeOut();
 jQuery('.' + sectionName + 'Section').fadeIn();
 jQuery(el).addClass("highlightSearchOption");
 var normalShade = getShade(el, 1, 0);
 var lighterShade = getShade(el, 1.77, 0.87);
 if(lighterShade) {
	 jQuery("#Inlay").css("border-top", "2px solid " + normalShade);
	 jQuery("#Inlay").css("border-bottom", "2px dotted " + normalShade);
	 jQuery("#Inlay").css("background-color", lighterShade);
	}
 if(sectionName == "search") {
	jQuery("#functionFilter").hide();
 }
 else {
	jQuery("#functionFilter").show();
 }
 return true;
}

function highLightFilters() {
	jQuery("#filterOptions input").parent().removeClass("highlightTickBox");
	jQuery("#filterOptions input[@checked]").parent().addClass("highlightTickBox");
	jQuery("#functionFilter .Actions").fadeIn();
	jQuery("#filterOptions label.left").hide();
}


function showProductSection(idNumber, element) {
	jQuery("#productFilter a").removeClass("highlightProductFilter");
	jQuery(element).addClass("highlightProductFilter");
	if(idNumber) {
		jQuery(".CertificationGroup").hide();
		jQuery(".ProductCertificationGroup" + idNumber).fadeIn();
	}
	else {
		jQuery(".CertificationGroup").fadeIn();
	}
	return true;
}


function hideProductFilterOption() {
	jQuery('.filterOption').hide();
	jQuery('.filterDetails').slideDown();
	return true;
}
