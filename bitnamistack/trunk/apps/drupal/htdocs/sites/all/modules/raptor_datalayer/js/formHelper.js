/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

function setAsPickFromList(rootname)
{
    //alert('set as pickfromlist');
    
    var sName = rootname + 'id';
    var oListBox=document.getElementsByName(sName);
    oListBox[0].style.display = 'inline';
    
    var sName = rootname + 'makelistpickbtn';
    var oTB=document.getElementsByName(sName);
    oTB[0].style.display = 'none';

    var sName = rootname + 'customtx';
    var oTB=document.getElementsByName(sName);
    oTB[0].style.display = 'none';
    sText = oTB[0].value;
    oTB[0].value = '';  //20140724
    selectMatchingListText(oListBox[0], sText);

    var sName = rootname + 'makecustombtn';
    var oTB=document.getElementsByName(sName);
    oTB[0].style.display = 'inline';
    
}

function setAsCustomText(rootname)
{
    //alert('set as customtext');

    var sName = rootname + 'id';
    var oListBox=document.getElementsByName(sName);
    oListBox[0].style.display = 'none';
    sText = getSelectedText(sName);
    oListBox[0].selectedIndex = -1; //Clear selection 20140724
    
    var sName = rootname + 'makelistpickbtn';
    var oTB=document.getElementsByName(sName);
    oTB[0].style.display = 'inline';

    var sName = rootname + 'customtx';
    var oTB=document.getElementsByName(sName);
    oTB[0].style.display = 'inline';
    oTB[0].value = sText;

    var sName = rootname + 'makecustombtn';
    var oTB=document.getElementsByName(sName);
    oTB[0].style.display = 'none';

}

function clearListOptionsByID(sID)
{
    var oList=document.getElementById(sID);
    if (oList == null) return;
    if (oList.options == null) return;
    oList.options.length = 0;	 // That's it!
}


function setListOptionsByID(sID, aOptions)
{
    //alert('!!! look set options for ' + sID);
    clearListOptionsByID(sID);
    var oList=document.getElementById(sID);
    
    var optn = document.createElement("OPTION");
    optn.text = '';
    optn.value = '';
    oList.options.add(optn);
    for(var i=0; i < aOptions.length; i++)
    {
        var optn = document.createElement("OPTION");
        optn.text = aOptions[i];
        optn.value = aOptions[i];
        oList.options.add(optn);
    }
}

function getSelectedText(sName) 
{
    var oList=document.getElementsByName(sName);

    if (oList[0].selectedIndex === -1)
    {
        return null;
    }

    return oList[0].options[oList[0].selectedIndex].text;
}

function selectMatchingListText(oListBox, sMatchText) 
{
    //alert('will look for matching ' + sMatchText);
    for (var i=0; i<oListBox.length; i++) 
    {
        if (oListBox.options[i].text === sMatchText)
        {
            //alert('found mtach!');
            oListBox.selectedIndex = i;
            return;
        }
    }
    //alert('Did NOT find match!');
    oListBox.selectedIndex = -1;
    return
}

/**
 * By convention, a section has value -1 if no values defined for it.
 * @param {type} sSectionName
 * @param {type} aTemplateData
 * @returns boolean
 */
function sectionHasDefaultValues(sSectionName, aTemplateData)
{
    //alert('look check section ' + sSectionName)
    var bResult = aTemplateData[sSectionName] !== -1;
    return (bResult);
}

/**
 * Custom jQuery function so we can call our script from Drupal Ajax command.
 */
(function($) {
$.fn.doDefaultValuesInFromProtocolTemplate = function() {
    setDefaultValuesInFromProtocolTemplate();
};
})(jQuery);


/**
 * Custom jQuery function so we can call our script from Drupal Ajax command.
 */
(function($) {
$.fn.doOptionValuesForProtocolLibForm = function() {
    setOptionValuesForProtocolLibForm();
};
})(jQuery);

function clearOptionValuesInSection(sSectionName)
{
    //TODO
}

function sectionHasOptionValues(sSectionName, aAllOptionData)
{
    var bResult = aAllOptionData[sSectionName] !== -1;
    //alert('look check section ' + sSectionName + ' is ' + bResult);
    return (bResult);
}

function setOptionValuesInSection(sSectionName, aAllOptionData)
{
    if(sSectionName === 'hydration')
    {
        clearValuesInSection(sSectionName);
        if(aAllOptionData.hydration !== -1)
        {
            //Set it to the values in template data.
            var aOralValue = aAllOptionData.hydration.oral;
            var aIVValue = aAllOptionData.hydration.iv;
            setListOptionsByID('edit-hydration-oral-id', aOralValue);
            setListOptionsByID('edit-hydration-iv-id', aIVValue);
        }
    } else
    if(sSectionName === 'sedation')
    {
        clearValuesInSection(sSectionName);
        if(aAllOptionData.sedation !== -1)
        {
            //Set it to the values in template data.
            var aOralValue = aAllOptionData.sedation.oral;
            var aIVValue = aAllOptionData.sedation.iv;
            setListOptionsByID('edit-sedation-oral-id', aOralValue);
            setListOptionsByID('edit-sedation-iv-id', aIVValue);
        }
    } else 
    if(sSectionName === 'contrast')
    {
        clearValuesInSection(sSectionName);
        if(aAllOptionData.contrast !== -1)
        {
            //Set it to the values in template data.
            var aEntericValue = aAllOptionData.contrast.enteric;
            var aIVValue = aAllOptionData.contrast.iv;
            setListOptionsByID('edit-contrast-enteric-id', aEntericValue);
            setListOptionsByID('edit-contrast-iv-id', aIVValue);
        }
    } else 
    if(sSectionName === 'radioisotope')
    {
        clearValuesInSection(sSectionName);
        if(aAllOptionData.radioisotope !== -1)
        {
            //Set it to the values in template data.
            var aEntericValue = aAllOptionData.radioisotope.enteric;
            var aIVValue = aAllOptionData.radioisotope.iv;
            setListOptionsByID('edit-radioisotope-enteric-id', aEntericValue);
            setListOptionsByID('edit-radioisotope-iv-id', aIVValue);
        }
    }
}

function getAllOptionDataJSON()
{
    var realdata = $("#json-option-values-all-sections").html();
    //alert("look real data is " + realdata);
    aOptionsData = realdata;
    if((typeof aOptionsData) == 'string')
    {
        aOptionsData = eval("(" + aOptionsData + ")");
    }
    return aOptionsData;
}

function setOptionValuesForProtocolLibForm()
{
    alert('1Called setOptionValuesForProtocolLibForm!');
    
    aAllOptionData = getAllOptionDataJSON();
    clearOptionValuesInSection('hydration'); 
    clearOptionValuesInSection('sedation'); 
    clearOptionValuesInSection('contrast'); 
    clearOptionValuesInSection('radioisotope'); 
    if(sectionHasOptionValues('hydration',aAllOptionData))
    {
        setOptionValuesInSection('hydration',aAllOptionData); 
    }
    if(sectionHasOptionValues('sedation',aAllOptionData))
    {
        setOptionValuesInSection('sedation',aAllOptionData); 
    }
    if(sectionHasOptionValues('contrast',aAllOptionData))
    {
        setOptionValuesInSection('contrast',aAllOptionData); 
    }
    if(sectionHasOptionValues('radioisotope',aAllOptionData))
    {
        setOptionValuesInSection('radioisotope',aAllOptionData); 
    }
    
    alert('3Called setOptionValuesForProtocolLibForm!');
}

function getTemplateDataJSON()
{
    var realdata = $("#json-default-values-all-sections").html();
    //alert("look real data is " + realdata);
    aTemplateData = realdata;
    if((typeof aTemplateData) == 'string')
    {
        aTemplateData = eval("(" + aTemplateData + ")");
    }
    return aTemplateData;
}

//Copied from protocol.js
function disableProtocolControls() 
{
    Drupal.pageData.disableLinks = true;
    $('input, select, textarea')
        .not('#edit-protocol1-nm')
        .attr('disabled', 'disabled');
    $('a[href^=javascript]').each(function (index, element) {
        $(element)
            .attr('data-onclick', $(this).attr('href'))
            .attr('href', 'javascript:void();');
    })
}

//Copied from protocol.js
function enableProtocolControls() 
{
    Drupal.pageData.disableLinks = false;
    $('input, select, textarea')
        .not('#edit-protocol1-nm, .container-inline select, #edit-contrast-fieldset select')
        .removeAttr('disabled');
    $('[data-onclick]').each(function (index, element) {
        $(element)
            .attr('href', $(this).attr('data-onclick'))
            .removeAttr('data-onclick');
    })
}

/**
 * Set or clear section based on content of template data array.
 */
function setDefaultValuesInFromProtocolTemplate()
{
    //alert("1running setDefaultValuesInFromProtocolTemplate");
    
    //disableProtocolControls();

    aTemplateData = getTemplateDataJSON();
    clearValuesInSection('hydration'); 
    clearValuesInSection('sedation'); 
    clearValuesInSection('contrast'); 
    clearValuesInSection('radioisotope'); 
    clearValuesInSection('consentreq'); 
    clearValuesInSection('protocolnotes'); 
    if(sectionHasDefaultValues('hydration',aTemplateData))
    {
        setDefaultValuesInSection('hydration',aTemplateData); 
    }
    if(sectionHasDefaultValues('sedation',aTemplateData))
    {
        setDefaultValuesInSection('sedation',aTemplateData); 
    }
    if(sectionHasDefaultValues('contrast',aTemplateData))
    {
        setDefaultValuesInSection('contrast',aTemplateData); 
    }
    if(sectionHasDefaultValues('radioisotope',aTemplateData))
    {
        setDefaultValuesInSection('radioisotope',aTemplateData); 
    }
    if(sectionHasDefaultValues('consentreq',aTemplateData))
    {
        setDefaultValuesInSection('consentreq',aTemplateData); 
    }
    if(sectionHasDefaultValues('protocolnotes',aTemplateData))
    {
        setDefaultValuesInSection('protocolnotes',aTemplateData); 
    }
    
    //enableProtocolControls();
    //alert('look at bottom of set default values!');
}

function selectListItem(oMyList, sValueToSelect)
{
    oMyList.selectedIndex = -1; //Clear it to start
    for (var i = 0; i < oMyList.options.length; i++) 
    {
        if (oMyList.options[i].value === sValueToSelect) 
        {
            oMyList.selectedIndex = i;
            break;
        }
    }
    return oMyList.selectedIndex;
}

function setAckRequiredForSection(sSectionName)
{
    //alert('about to require ack for ' + sSectionName);
    var sID = "acknowledge_" + sSectionName + "_group";
    var oGrp=document.getElementById(sID);
    oGrp.style.display = 'inline';
    var sName = 'require_acknowledgement_for_' + sSectionName;
    var oTB=document.getElementsByName(sName);
    oTB[0].value = 'yes';
    //alert('done require ack for ' + sSectionName);
}

/**
 * Call this to set the values of a "radio button" type input section.
 * @param {type} sSectionName
 * @param {type} radioidroot
 * @param {type} idroot
 * @param {type} ackid
 * @param {type} sOralValue
 * @param {type} sIVValue
 * @returns {undefined}
 */
function setDefaultInRadioTypeSection(sSectionName, radioidroot, idroot, ackid, sOralValue, sIVValue)
{
    var checkbox = document.getElementById(ackid);
    checkbox.checked = false;
    var radiobtn = document.getElementById(radioidroot + 'none');
    radiobtn.checked = true;     
    var sListID = idroot + 'oral-id';
    var oMyList=document.getElementById(sListID);
    var sValueToSelect = sOralValue;
    var nListIndex = -1;
    if(sValueToSelect !== undefined && sValueToSelect !== null && sValueToSelect !== -1)
    {
        activateRadioButton(radioidroot + 'oral');
        nListIndex = selectListItem(oMyList, sValueToSelect);
        if(nListIndex < 0)
        {
            //Convert control to custom text
            setAsCustomText(sSectionName + '_oral_');
            var oMyTextbox=document.getElementById(idroot + 'oral-customtx');
            oMyTextbox.value = sValueToSelect;
        }
    }
    var sListID = idroot + 'iv-id';
    var oMyList=document.getElementById(sListID);
    var sValueToSelect = sIVValue;
    var nListIndex = -1;
    if(sValueToSelect !== undefined && sValueToSelect !== null && sValueToSelect !== -1)
    {
        activateRadioButton(radioidroot + 'iv');
        selectListItem(oMyList, sValueToSelect);
        if(nListIndex < 0)
        {
            //Convert control to custom text
            setAsCustomText(sSectionName + '_iv_');
            var oMyTextbox=document.getElementById(idroot + 'iv-customtx');
            oMyTextbox.value = sValueToSelect;
        }
    }

    //Require acknowledgement.
    setAckRequiredForSection(sSectionName);
}

/**
 * Call this to set the values of a "checkbox" type input section.
 * @param {type} sSectionName
 * @param {type} radioidroot
 * @param {type} idroot
 * @param {type} ackid
 * @param {type} sOralValue
 * @param {type} sIVValue
 * @returns {undefined}
 */
function setDefaultInCheckboxTypeSection(sSectionName, radioidroot, idroot, ackid, sEntericValue, sIVValue)
{
    var checkbox = document.getElementById(ackid);
    checkbox.checked = false;
    var radiobtn = document.getElementById(radioidroot + 'none');
    activateCheckbox(radioidroot + 'none');    //20140724 Important that we do this at least once.     
    clearCheckbox(radioidroot + 'none');       //20140724 Now we clear it. 
    
    //Enteric
    var sListID = idroot + 'enteric-id';
    var oMyList=document.getElementById(sListID);
    var sValueToSelect = sEntericValue;
    var nListIndex = -1;
    if(sValueToSelect !== undefined && sValueToSelect !== null && sValueToSelect !== -1)
    {
        activateCheckbox(radioidroot + 'enteric');
        nListIndex = selectListItem(oMyList, sValueToSelect);
        if(nListIndex < 0)
        {
            //Convert control to custom text
            setAsCustomText(sSectionName + '_enteric_');
            var oMyTextbox=document.getElementById(idroot + 'enteric-customtx');
            oMyTextbox.value = sValueToSelect;
        }
    }
    
    //IV
    var sListID = idroot + 'iv-id';
    var oMyList=document.getElementById(sListID);
    var sValueToSelect = sIVValue;
    var nListIndex = -1;
    if(sValueToSelect !== undefined && sValueToSelect !== null && sValueToSelect !== -1)
    {
        activateCheckbox(radioidroot + 'iv');
        selectListItem(oMyList, sValueToSelect);
        if(nListIndex < 0)
        {
            //Convert control to custom text
            setAsCustomText(sSectionName + '_iv_');
            var oMyTextbox=document.getElementById(idroot + 'iv-customtx');
            oMyTextbox.value = sValueToSelect;
        }
    }

    //Require acknowledgement.
    setAckRequiredForSection(sSectionName);
}

/**
 * Call this to reset the values of a section back to their defaults
 * @param {type} sSectionName
 * @param {type} aTemplateData
 * @returns {undefined}
 */
function setDefaultValuesInSection(sSectionName, aTemplateData)
{
//alert('INTO debug look in ' + sSectionName + ' set default values!-->' + aTemplateData);
    if(sSectionName === 'hydration')
    {
        clearValuesInSection(sSectionName);
        if(aTemplateData.hydration !== -1)
        {
            //Set it to the values in template data.
            var radioidroot = 'edit-hydration-cd-';
            var idroot = 'edit-hydration-';
            var ackid = 'edit-acknowledge-hydration';
            var sOralValue = aTemplateData.hydration.oral;
            var sIVValue = aTemplateData.hydration.iv;
            setDefaultInRadioTypeSection(sSectionName, radioidroot, idroot, ackid, sOralValue, sIVValue);            
        }
    } else
    if(sSectionName === 'sedation')
    {
        clearValuesInSection(sSectionName);
        if(aTemplateData.sedation !== -1)
        {
            //Set it to the values in template data.
            var radioidroot = 'edit-sedation-cd-';
            var idroot = 'edit-sedation-';
            var ackid = 'edit-acknowledge-sedation';
            var sOralValue = aTemplateData.sedation.oral;
            var sIVValue = aTemplateData.sedation.iv;
            setDefaultInRadioTypeSection(sSectionName, radioidroot, idroot, ackid, sOralValue, sIVValue);            
        }
    } else 
    if(sSectionName === 'contrast')
    {
        clearValuesInSection(sSectionName);
        if(aTemplateData.contrast !== -1)
        {
            //Set it to the values in template data.
            var radioidroot = 'edit-contrast-cd-';
            var idroot = 'edit-contrast-';
            var ackid = 'edit-acknowledge-contrast';
            var sEntericValue = aTemplateData.contrast.enteric;
            var sIVValue = aTemplateData.contrast.iv;
            setDefaultInCheckboxTypeSection(sSectionName, radioidroot, idroot, ackid, sEntericValue, sIVValue);            
        }
    } else 
    if(sSectionName === 'radioisotope') //20140723
    {
        clearValuesInSection(sSectionName);
        if(aTemplateData.radioisotope !== -1)
        {
            //Set it to the values in template data.
            var radioidroot = 'edit-radioisotope-cd-';
            var idroot = 'edit-radioisotope-';
            var ackid = 'edit-acknowledge-radioisotope';
            var sEntericValue = aTemplateData.radioisotope.enteric;
            var sIVValue = aTemplateData.radioisotope.iv;
            setDefaultInCheckboxTypeSection(sSectionName, radioidroot, idroot, ackid, sEntericValue, sIVValue);            
        }
    } else
    if(sSectionName === 'consentreq')
    {
        if(aTemplateData.consentreq === -1)
        {
            //Clear it all.
            clearValuesInSection(sSectionName);
        } else {
            var sName = 'require_acknowledgement_for_' + sSectionName;
            var oTB=document.getElementsByName(sName);
            oTB[0].value = 'yes';
            
            //Set it to the values in template data.
            var checkbox = document.getElementById("edit-acknowledge-consentreq");
            checkbox.checked = false;
            var sValueToSelect = aTemplateData.consentreq;
            if(sValueToSelect !== undefined && sValueToSelect !== null)
            {
                if(sValueToSelect === 'yes')
                {
                    activateRadioButton("edit-consentreq-cd-yes");
                } else if(sValueToSelect === 'no')
                {
                    activateRadioButton("edit-consentreq-cd-no");
                } else {
                    activateRadioButton("edit-consentreq-cd-unknown"); //20140717
                }
            }

            //Require acknowledgement.
            setAckRequiredForSection(sSectionName);
        }
    } else
    if(sSectionName === 'protocolnotes')
    {
        if(aTemplateData.protocolnotes === -1)
        {
            //Clear it all.
            clearValuesInSection(sSectionName);
        } else {
            var sName = 'require_acknowledgement_for_' + sSectionName;
            var oTB=document.getElementsByName(sName);
            oTB[0].value = 'yes';
            
            //Set it to the values in template data.
            var sValue = aTemplateData.protocolnotes.text;
            if(sValue !== undefined && sValue !== null)
            {
                var textbox = document.getElementById('edit-protocolnotes-tx');
                textbox.value = sValue;
                var checkbox = document.getElementById('edit-acknowledge-protocolnotes');
                checkbox.checked = false;
            }

            //Require acknowledgement.
            setAckRequiredForSection(sSectionName);
        }
    }
    //alert('OUTOF debug look in ' + sSectionName + ' set default values!-->' + aTemplateData);
}

/**
 * Clear all the values in the section.
 * @param {type} sSectionName
 * @returns {undefined}
 */
function clearValuesInSection(sSectionName)
{
    //Start by declaring that we do not have default values selected.
    notDefaultValuesInSection(sSectionName);    

    //Now clear all the controls in the section.
    if(sSectionName === 'hydration')
    {
        setAsPickFromList('hydration_oral_');
        setAsPickFromList('hydration_iv_');
        clearRadioGroup('edit-hydration-cd-none');
        var checkbox = document.getElementById('edit-acknowledge-hydration');
        checkbox.checked = false;
    } else
    if(sSectionName === 'sedation')
    {
        setAsPickFromList('sedation_oral_');
        setAsPickFromList('sedation_iv_');
        clearRadioGroup('edit-sedation-cd-none');
        var checkbox = document.getElementById('edit-acknowledge-sedation');
        checkbox.checked = false;
    } else
    if(sSectionName === 'consentreq')
    {
        clearRadioGroup('edit-consentreq-cd-unknown');
        var radiobtn = document.getElementById('edit-consentreq-cd-unknown');
        radiobtn.checked = false;   //Now uncheck it too       
    } else
    if(sSectionName === 'radioisotope')
    {
        //TODO setAsPickFromList
        clearCheckbox('edit-radioisotope-cd-enteric');
        clearCheckbox('edit-radioisotope-cd-iv');
        clearCheckbox('edit-radioisotope-cd-none');
       
        var checkbox = document.getElementById("edit-acknowledge-contrast");
        checkbox.checked = false;
    } else
    if(sSectionName === 'contrast')
    {
        //TODO setAsPickFromList
        clearCheckbox('edit-contrast-cd-enteric');
        clearCheckbox('edit-contrast-cd-iv');
        clearCheckbox('edit-contrast-cd-none');
       
        var checkbox = document.getElementById("edit-acknowledge-contrast");
        checkbox.checked = false;
    } else
    if(sSectionName === 'protocolnotes')
    {
        //Fixed 20140710
        var textbox = document.getElementById('edit-protocolnotes-tx');
        textbox.value = '';

        var checkbox = document.getElementById("edit-acknowledge-protocolnotes");
        checkbox.checked = false;
    }
       
}

/**
 * Pass in the ID of the radio button
 * @param ID mainradioid
 */
function activateRadioButton(mainradioid)
{
    $( "#" + mainradioid ).trigger( "click" );  //So it cascades the event
}

/**
 * Pass in the ID of the checkbox
 * @param ID mainradioid
 */
function activateCheckbox(mainradioid)
{
    $( "#" + mainradioid ).trigger( "click" );  //So it cascades the event
}

/**
 * Pass in the ID of the radio button that has no other controls activated by it.
 * @param ID mainradioid
 */
function clearRadioGroup(mainradioid)
{
    activateRadioButton(mainradioid);
    var radiobtn = document.getElementById(mainradioid);
    radiobtn.checked = false;   //Now uncheck it too       
}

/**
 * Clear a checkbox such that it triggers cascading events
 * @param ID checkboxid
 */
function clearCheckbox(checkboxid)
{
    //alert('clearing ' + checkboxid);
    var checkbox = document.getElementById(checkboxid);
    checkbox.checked = true;   //Turn it on first     
    $( "#" + checkboxid ).trigger( "click" );  //So it cascades the event
    //alert('cleared ' + checkboxid);
}

/**
 * Set a checkbox such that it triggers cascading events
 * @param ID checkboxid
 */
function setCheckbox(checkboxid)
{
    var checkbox = document.getElementById(checkboxid);
    checkbox.checked = false;   //Turn it on first     
    $( "#" + checkboxid ).trigger( "click" );  //So it cascades the event
}

    
/**
 * Call this when no longer using default values in a section
 * @param {type} sSectionName
 */
function notDefaultValuesInSection(sSectionName)
{
    var sID = "acknowledge_" + sSectionName + "_group";
    var oGrp=document.getElementById(sID);
    oGrp.style.display = 'none';
    
    var sName = 'require_acknowledgement_for_' + sSectionName;
    var oTB=document.getElementsByName(sName);
    oTB[0].value = 'no';
}

/**
 * Call this to place text into the textbox
 */
function setTextboxByName(sName, sValue)
{
    var oTB=document.getElementsByName(sName);
    oTB[0].value = sValue;
}

/**
 * Call this to place text into the textarea
 * @param {type} sName
 * @param {type} sAppend
 * @returns {undefined}
 */
function app2textareaByName(sName,sAppend)
{
    var oTB=document.getElementsByName(sName);
    if(oTB[0].value !== '')
    {
            oTB[0].value = oTB[0].value + "\n";
    }
    oTB[0].value = oTB[0].value + sAppend;
}

/**
 * Call this to place text into the textarea
 * @param {type} sID
 * @param {type} sAppend
 * @returns {undefined}
 */
function app2textareaByID(sID,sAppend)
{
    var oTB=document.getElementById(sID);
    if(oTB.value !== '')
    {
            oTB.value = oTB.value + "\n";
    }
    oTB.value = oTB.value + sAppend;
}

/**
 * The explanation text is contained in a hidden tag.  Show it in a popup.
 * @param number nExplanationTagID is the ID of the tag containing the text
 */
function showContraIndicationsExplanationPopup(nExplanationTagID)
{
    var msg;
    msg = $('#' + nExplanationTagID).text();
    alert(msg);
}