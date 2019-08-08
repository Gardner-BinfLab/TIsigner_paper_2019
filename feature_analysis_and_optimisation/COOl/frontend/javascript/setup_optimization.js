function recalculateShowHide() {
	var optimize_ic0 = document.getElementById("optimize_ic0");
	var optimize_ic1 = document.getElementById("optimize_ic1");
	var optimize_ic2 = document.getElementById("optimize_ic2");
	var optimize_cc0 = document.getElementById("optimize_cc0");
	var optimize_cc1 = document.getElementById("optimize_cc1");
	var optimize_cc2 = document.getElementById("optimize_cc2");
	var optimize_cai0 = document.getElementById("optimize_cai0");
	var optimize_cai1 = document.getElementById("optimize_cai1");
	var optimize_cai2 = document.getElementById("optimize_cai2");
	var use_custom_species0 = document.getElementById("use_custom_species0");
	var use_custom_species1 = document.getElementById("use_custom_species1");
	var ic_frequency = document.getElementById("ic_frequency");
	var cc_frequency = document.getElementById("cc_frequency");
	
	//These 3 are <tbody> elements that need to be shown/hidden
	var translation_rules = document.getElementById("custom_species_translation_rules");
	var ic_optional = document.getElementById("ic_optional");
	var individual_codon = document.getElementById("custom_species_individual_codon");
	var cc_optional = document.getElementById("cc_optional");
	var codon_context = document.getElementById("custom_species_codon_context");
	
	
	//Check whether to display Individual Codon
	if (use_custom_species1.checked) {
		//If Using Custom Species
		translation_rules.style.display = '';				//Translation Rules always shown
		
		//Checks for IC box
		if (												//If IC or CAI is maximize or minimize
			optimize_ic1.checked || optimize_ic2.checked || optimize_cai1.checked || optimize_cai2.checked
		) {	
			individual_codon.style.display = '';			//Show field
			ic_optional.style.display = 'none';				//HIDE optional
		} else {											//Otherwise Checkbox is not checked
			ic_optional.style.display = '';					//SHOW optional
			if (ic_frequency.value != "") {					//If field has content
				individual_codon.style.display = '';		//Show field
			} else {										//Otherwise box is not checked and field is empty
				individual_codon.style.display = 'none';	//Hide Field
			}
		}
		
		//Checks for CC box
		if (optimize_cc1.checked || optimize_cc2.checked) {	//If CC is maximize or minimize
			codon_context.style.display = '';				//Show field
			cc_optional.style.display = 'none';				//HIDE optional
		} else {
			cc_optional.style.display = '';					//SHOW optional
			if (cc_frequency.value != "") {					//Otherwise if field has content
				codon_context.style.display = '';			//Show field
			} else {										//Otherwise box is not checked and field is empty
				codon_context.style.display = 'none';		//Hide Field
			}
		}
		
	} else {
		//If NOT using custom species
		translation_rules.style.display = 'none';		//Translation Rules always hidden
		
		//Checks for IC box
		ic_optional.style.display = '';					//SHOW optional
		if (ic_frequency.value != "") {					//Otherwise if has content
			individual_codon.style.display = '';		//Show field
		} else {										//Otherwise field is empty
			individual_codon.style.display = 'none';	//Hide Field
		}
		
		//Checks for CC box
		cc_optional.style.display = '';					//SHOW optional
		if (cc_frequency.value != "") {					//If field has content
			codon_context.style.display = '';			//Show field
		} else {										//Otherwise field is empty
			codon_context.style.display = 'none';		//Hide Field
		}
		
	}
	
	//Templates:
	//translation_rules.style.display = 'none';
	//translation_rules.style.display = 'block';
	//translation_rules.style.display = 'inline';
}

jQuery(document).ready(			//When document is ready
	function(){
		recalculateShowHide();	//Show/Hide appropriate elements
	}
);


