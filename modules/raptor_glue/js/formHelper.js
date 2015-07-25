/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

var g_protocol_id_map = {initialized:false};
var g_exam_id_map = {initialized:false};


/**
 * Custom jQuery functions so we can call our script from Drupal Ajax command.
 */
(function($) {
$.fn.doDefaultValuesInFromProtocolTemplate = function() {
    setDefaultValuesInFromProtocolTemplate();
};
})(jQuery);
(function($) {
$.fn.doSetEnabledDisabledInContrastSection = function() {
    setEnabledDisabledInCheckboxTypeSection('contrast');
};
})(jQuery);
(function($) {
$.fn.doSetEnabledDisabledInRadioisotopeSection = function() {
    setEnabledDisabledInCheckboxTypeSection('radioisotope');
};
})(jQuery);
(function($) {
$.fn.doOptionValuesForProtocolLibForm = function() {
    setOptionValuesForProtocolLibForm();
};
})(jQuery);


/**
 * Hide the entire comment panel if the wrong radio is checked.
 */
function manageChecklistQuestionCommentByName(sRadioValue,sShowOnValues,sCommentBoxName)
{
    //alert('START NOW checklist manage inputs>>>['+sRadioValue+'] showOnValues=('+sShowOnValues+') ('+sCommentBoxName+')');
    var sFind = "["+sRadioValue+"]";
    var nPos = sShowOnValues.indexOf(sFind);
    var oTB=document.getElementsByName(sCommentBoxName);
    var oWrapper=document.getElementsByName(sCommentBoxName+'-wrapper');
    if(nPos > -1 || oTB[0].value > '')
    {
        //Show the comment box.
        oWrapper[0].style.display = 'inline';
        
    } else {
        //Do NOT show the comment box.
        oWrapper[0].style.display = 'none';
    }
    //alert('DONE NOW checklist manage inputs>>>['+sRadioValue+'] showOnValues=('+sShowOnValues+') ('+sCommentBoxName+')>>> style>>>'+oWrapper[0].style.display);
}

/**
 * Call this to enable/disable the custom controls.
 */
function setComboInputStateByName(rootname, bDisable)
{
    var sName = rootname + 'id';
    var oListBox=document.getElementsByName(sName);
    
    var sName = rootname + 'makecustombtn';
    var oButton1=document.getElementsByName(sName);
    
    oListBox[0].disabled = bDisable;
    oButton1[0].disabled = bDisable;
    
    var sName = rootname + 'customtx';
    var oCustomTB=document.getElementsByName(sName);

    var sName = rootname + 'makelistpickbtn';
    var oButton2=document.getElementsByName(sName);
    
    oCustomTB[0].disabled = bDisable;
    oButton2[0].disabled = bDisable;
}

/**
 * Call this to reset and disable the custom controls.
 */
function resetComboInputByName(rootname, bDisable)
{
    setAsPickFromListByName(rootname);
    
    var sName = rootname + 'id';
    var oListBox=document.getElementsByName(sName);
    
    var sName = rootname + 'makecustombtn';
    var oTB=document.getElementsByName(sName);
    
    oListBox[0].disabled = bDisable;
    oTB[0].disabled = bDisable;
   
}


function setAsPickFromListByName(rootname)
{
    //alert('set as pickfromlist with ['+rootname+']');

    //Set the new input mode
    var sName = rootname + '_inputmode';
    var oTB=document.getElementsByName(sName);
    oTB[0].value = 'list';

    //Now get and fix the list
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
    
    //alert('done as pickfromlist with ['+rootname+']');
}

/**
 * Uses names rather than ID to find controls. 
 */
function setAsCustomTextByName(rootname)
{
    //Set the new input mode
    var sName = rootname + '_inputmode';
    var oTB=document.getElementsByName(sName);
    oTB[0].value = 'customtx';

    //Get and change the list.
    var sName = rootname + 'id';
    var oListBox=document.getElementsByName(sName);
    oListBox[0].style.display = 'none';
    sText = getSelectedTextByName(sName);
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

    //alert('done set as customtext for ' + rootname);

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

function getSelectedTextByName(sName) 
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
 * @returns boolean
 */
function templateSectionHasDefaultValues(sectionname, aTemplateData)
{
    //alert('look check section ' + sectionname)
    var bResult = aTemplateData[sectionname] !== -1;
    return (bResult);
}


function clearOptionValuesInSection(sectionname)
{
    //TODO
}

function sectionHasOptionValues(sectionname, aAllOptionData)
{
    var bResult = aAllOptionData[sectionname] !== -1;
    //alert('look check section ' + sectionname + ' is ' + bResult);
    return (bResult);
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
    //alert('1Called setOptionValuesForProtocolLibForm!');
    
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
    
    //alert('3Called setOptionValuesForProtocolLibForm!');
}

/**
 * Get real ID values for a radio buttons in section
 */
function getRadioControlIDMap(sectionname,valuename1,valuename2,valuename3)
{
    var real_id1 = $(":input[id^='edit-"+sectionname+"-radio-cd-"+valuename1+"']").attr('id');
    var real_id2 = $(":input[id^='edit-"+sectionname+"-radio-cd-"+valuename2+"']").attr('id');
    var real_id3 = $(":input[id^='edit-"+sectionname+"-radio-cd-"+valuename3+"']").attr('id');
    var id_map_root = {radio: {}};
    id_map_root['radio']['id_'+valuename1] = real_id1;
    id_map_root['radio']['id_'+valuename2] = real_id2;
    id_map_root['radio']['id_'+valuename3] = real_id3;
    
    id_map_root['acknowledge'] = getAcknowledgementControlIDMap(sectionname);
    
    return id_map_root;
}

/**
 * Get real ID values for teh checkboxe buttons in section
 */
function getCheckboxControlIDMap(sectionname,valuename1,valuename2,valuename3)
{
    var real_id1 = $(":input[id^='edit-"+sectionname+"-cd-"+valuename1+"']").attr('id');
    var real_id2 = $(":input[id^='edit-"+sectionname+"-cd-"+valuename2+"']").attr('id');
    var real_id3 = $(":input[id^='edit-"+sectionname+"-cd-"+valuename3+"']").attr('id');
    var id_map_root = {checkbox: {}};
    id_map_root['checkbox']['id_'+valuename1] = real_id1;
    id_map_root['checkbox']['id_'+valuename2] = real_id2;
    id_map_root['checkbox']['id_'+valuename3] = real_id3;
    
    id_map_root['acknowledge'] = getAcknowledgementControlIDMap(sectionname);
    
    return id_map_root;
}


function getComboBoxControlIDMap(sectionname,valuename)
{
    var real_list_id = $(":input[id^='edit-"+sectionname+"-"+valuename+"-id']").attr('id');
    var real_text_id = $(":input[id^='edit-"+sectionname+"-"+valuename+"-customtx']").attr('id');

    var id_map_root = {};
    id_map_root['list'] = {};
    id_map_root['text'] = {};
    id_map_root['list']['id_'+valuename] = real_list_id;
    id_map_root['text']['id_'+valuename] = real_text_id;
    
    return id_map_root;
}

/**
 * Get real ID values for a radio button section
 */
function getRadioPanelControlIDMap(sectionname,valuename1,valuename2,valuename3)
{
    var id_map_root = getRadioControlIDMap(sectionname,valuename1,valuename2,valuename3);

    //Now get the other controls too.
    id_map_root['list'] = {};
    id_map_root['text'] = {};

    var map2 = getComboBoxControlIDMap(sectionname,valuename2);
    var map3 = getComboBoxControlIDMap(sectionname,valuename3);

    id_map_root['list']['id_'+valuename2] = map2['list']['id_'+valuename2];
    id_map_root['text']['id_'+valuename2] = map2['text']['id_'+valuename2];
    
    id_map_root['list']['id_'+valuename3] = map3['list']['id_'+valuename3];
    id_map_root['text']['id_'+valuename3] = map3['text']['id_'+valuename3];
    
    return id_map_root;
}

function getAcknowledgementControlIDMap(sectionname)
{
    var grpmatch = "div[id^='edit-default-values-grp-"+sectionname+"']";
    var reqmatch = ":input[id^='edit-require-acknowledgement-for-"+sectionname+"']";
            
//alert('Will try to match ['+grpmatch+']');
    var real_ackgrp_id = $(grpmatch).attr('id');
//alert('Tried to match ['+grpmatch+'] result =['+real_ackgrp_id+']');
    var real_ackchk_id = $(":input[id^='edit-acknowledge-"+sectionname+"']").attr('id');
    var real_ackreq_id = $(reqmatch).attr('id');
    
    var id_map_root = {};
    id_map_root['group'] = real_ackgrp_id;
    id_map_root['checkbox'] = real_ackchk_id;
    id_map_root['required'] = real_ackreq_id;
    return id_map_root;
}

/**
 * Get real ID values for a checkbox button section
 */
function getCheckboxPanelControlIDMap(sectionname,valuename1,valuename2,valuename3)
{
    var id_map_root = getCheckboxControlIDMap(sectionname,valuename1,valuename2,valuename3);

    //Now get the other controls too.
    id_map_root['list'] = {};
    id_map_root['text'] = {};

    var map2 = getComboBoxControlIDMap(sectionname,valuename2);
    var map3 = getComboBoxControlIDMap(sectionname,valuename3);

    id_map_root['list']['id_'+valuename2] = map2['list']['id_'+valuename2];
    id_map_root['text']['id_'+valuename2] = map2['text']['id_'+valuename2];
    
    id_map_root['list']['id_'+valuename3] = map3['list']['id_'+valuename3];
    id_map_root['text']['id_'+valuename3] = map3['text']['id_'+valuename3];
    
    return id_map_root;
}

function getNotesControlIDMap(sectionname)
{
    var real_id1 = $(":input[id^='edit-"+sectionname+"-tx']").attr('id');
    var id_map_root = {textarea: {}};
    id_map_root['textarea']['id'] = real_id1;
    
    id_map_root['acknowledge'] = getAcknowledgementControlIDMap(sectionname);
    
    return id_map_root;
}

/**
 * Get map to all the real ID values.
 */
function getProtocolControlIDMap()
{
    var themap = g_protocol_id_map;
    if(g_protocol_id_map.initialized != true)
    {
        //Initialize the map.
        themap['hydration'] = getRadioPanelControlIDMap('hydration','none','oral','iv');
        themap['sedation'] = getRadioPanelControlIDMap('sedation','none','oral','iv');
        themap['radioisotope'] = getCheckboxPanelControlIDMap('radioisotope','none','enteric','iv');
        themap['contrast'] = getCheckboxPanelControlIDMap('contrast','none','enteric','iv');
        themap['consentreq'] = getRadioControlIDMap('consentreq','unknown','no','yes');
        themap['protocolnotes'] = getNotesControlIDMap('protocolnotes');
        themap['examnotes'] = getNotesControlIDMap('examnotes');
        themap['initialized'] = true;
        //alert('LOOK constructed protocol map >>>' + themap.toSource());
    }
    return themap;
}

function getTemplateDataJSON()
{
    var realdata = $("#json-default-values-all-sections").html();
    aTemplateData = realdata;
    if((typeof aTemplateData) === 'string')
    {
        if(aTemplateData === null || aTemplateData.trim() === '')
        {
            alert('ERROR: Default values are missing!');
        } else {
            try
            {
                aTemplateData = eval("(" + aTemplateData + ")");
                if((typeof aTemplateData) !== 'object')
                {
                    alert('ERROR: Default values are missing or format cannot be converted into object!');
                }
            }
            catch(e) 
            {
                alert('ERROR: Trouble evaluating default values from [' + aTemplateData + '] because ' + e.message);
            }
        }
    } else {
        alert('ERROR: Default values are missing or format cannot be processed as a string!');
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
    var id_map = getProtocolControlIDMap();
    var id_cnone = id_map['contrast']['checkbox']['id_none'];
    var id_centeric = id_map['contrast']['checkbox']['id_enteric'];
    var id_civ = id_map['contrast']['checkbox']['id_iv'];
    var id_rnone = id_map['radioisotope']['checkbox']['id_none'];
    var id_renteric = id_map['radioisotope']['checkbox']['id_enteric'];
    var id_riv = id_map['radioisotope']['checkbox']['id_iv'];
    
    aTemplateData = getTemplateDataJSON();
    
    //Disable child value fields
    disableRadioGroupByName('hydration-radio-cd');
    disableRadioGroupByName('sedation-radio-cd');
    disableRadioGroupByName('consentreq-radio-cd');
    disable3CheckboxesByIds(id_cnone,id_centeric,id_civ);
    disable3CheckboxesByIds(id_rnone,id_renteric,id_riv);
 
    //Change the values.
    clearValuesInSection('hydration'); 
    clearValuesInSection('sedation'); 
    clearValuesInSection('contrast'); 
    clearValuesInSection('radioisotope'); 
    clearValuesInSection('consentreq'); 
    clearValuesInSection('protocolnotes'); 
    clearValuesInSection('examnotes'); 
    if(templateSectionHasDefaultValues('hydration',aTemplateData))
    {
        setDefaultValuesInSection('hydration',aTemplateData); 
    }
    if(templateSectionHasDefaultValues('sedation',aTemplateData))
    {
        setDefaultValuesInSection('sedation',aTemplateData); 
    }
    if(templateSectionHasDefaultValues('contrast',aTemplateData))
    {
        setDefaultValuesInSection('contrast',aTemplateData); 
    }
    if(templateSectionHasDefaultValues('radioisotope',aTemplateData))
    {
        setDefaultValuesInSection('radioisotope',aTemplateData); 
    }
    if(templateSectionHasDefaultValues('consentreq',aTemplateData))
    {
        setDefaultValuesInSection('consentreq',aTemplateData); 
    }
    if(templateSectionHasDefaultValues('protocolnotes',aTemplateData))
    {
        setDefaultValuesInSection('protocolnotes',aTemplateData); 
    }
    if(templateSectionHasDefaultValues('examnotes',aTemplateData))
    {
        setDefaultValuesInSection('examnotes',aTemplateData); 
    }

    //Enable the child value fields now
    enableRadioGroupByName('hydration-radio-cd');
    enableRadioGroupByName('sedation-radio-cd');
    enableRadioGroupByName('consentreq-radio-cd');
    enable3CheckboxesByIds(id_cnone,id_centeric,id_civ);
    enable3CheckboxesByIds(id_rnone,id_renteric,id_riv);
}

function clearListItem(oMyList)
{
    oMyList.selectedIndex = -1;
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

function setAckRequiredForSection(sectionname)
{
    var id_map = getProtocolControlIDMap();
    var id_ackgrp = id_map[sectionname]['acknowledge']['group'];
    if(typeof id_ackgrp !== 'undefined')
    {
        var id_ackchk = id_map[sectionname]['acknowledge']['checkbox'];
        var id_ackreq = id_map[sectionname]['acknowledge']['required'];
        var oGrp=document.getElementById(id_ackgrp);
        oGrp.style.display = 'inline';

        var oCB=document.getElementById(id_ackchk);
        oCB.checked = false;

        var oTB=document.getElementById(id_ackreq);
        oTB.value = 'yes';
    }
}

/**
 * Call this to set the values of a "radio button" type input section.
 */
function setDefaultInRadioTypeSection(sectionname, ackid, sOralValue, sIVValue)
{
    var id_map = getProtocolControlIDMap();
    var id_none = id_map[sectionname]['radio']['id_none'];
    var id_oral = id_map[sectionname]['radio']['id_oral'];
    var id_iv = id_map[sectionname]['radio']['id_iv'];
    var id_list_oral = id_map[sectionname]['list']['id_oral'];
    var id_text_oral = id_map[sectionname]['text']['id_oral'];
    var id_list_iv = id_map[sectionname]['list']['id_iv'];
    var id_text_iv = id_map[sectionname]['text']['id_iv'];

    var checkbox = document.getElementById(ackid);
    checkbox.checked = false;
    var radiobtn = document.getElementById(id_none);
    radiobtn.checked = true;     

    //Oral
    var oMyList=document.getElementById(id_list_oral);
    var sValueToSelect = sOralValue;
    var nListIndex = -1;
    if(sValueToSelect !== undefined && sValueToSelect !== null && sValueToSelect !== -1)
    {
        activateRadioButtonById(id_oral);   //radioidroot + 'oral');
        nListIndex = selectListItem(oMyList, sValueToSelect);
        if(nListIndex < 0)
        {
            //Convert control to custom text
            setAsCustomTextByName(sectionname + '_oral_');
            var oMyTextbox=document.getElementById(id_text_oral);   //idroot + 'oral-customtx');
            oMyTextbox.value = sValueToSelect;
        }
    }
    
    //IV
    var oMyList=document.getElementById(id_list_iv);
    var sValueToSelect = sIVValue;
    var nListIndex = -1;
    if(sValueToSelect !== undefined && sValueToSelect !== null && sValueToSelect !== -1)
    {
        activateRadioButtonById(id_iv); //radioidroot + 'iv');
        selectListItem(oMyList, sValueToSelect);
        if(nListIndex < 0)
        {
            //Convert control to custom text
            setAsCustomTextByName(sectionname + '_iv_');
            var oMyTextbox=document.getElementById(id_text_iv); //idroot + 'iv-customtx');
            oMyTextbox.value = sValueToSelect;
        }
    }

    //Require acknowledgement.
    setAckRequiredForSection(sectionname);
}

/**
 * Call this to set the values of a "checkbox" type input section.
 */
function setDefaultInCheckboxTypeSection(sectionname, ackid, sEntericValue, sIVValue)
{
//    alert('Started setDefaultInCheckboxTypeSection for ['+sectionname+']');

    var id_map = getProtocolControlIDMap();
    var id_none = id_map[sectionname]['checkbox']['id_none'];
    var id_enteric = id_map[sectionname]['checkbox']['id_enteric'];
    var id_iv = id_map[sectionname]['checkbox']['id_iv'];
    var id_list_enteric = id_map[sectionname]['list']['id_enteric'];
    var id_text_enteric = id_map[sectionname]['text']['id_enteric'];
    var id_list_iv = id_map[sectionname]['list']['id_iv'];
    var id_text_iv = id_map[sectionname]['text']['id_iv'];
    
    if(typeof ackid !== 'undefined')
    {
        var checkbox = document.getElementById(ackid);
        checkbox.checked = false;
    }
    var radiobtn = document.getElementById(id_none);    //radioidroot + 'none');
    clearCheckboxById(id_enteric);  //radioidroot + 'enteric');
    clearCheckboxById(id_iv);   //radioidroot + 'iv');
    activateCheckboxById(id_none);  //radioidroot + 'none');    //20140724 Important that we do this at least once.     
    clearCheckboxById(id_none); //radioidroot + 'none');       //20140724 Now we clear it. 
    
    var bFoundSomething = false;

    //Enteric
    var sListID = id_list_enteric;  //  idroot + 'enteric-id';
    var oMyList=document.getElementById(sListID);
    var oMyTextbox=document.getElementById(id_text_enteric);    //idroot + 'enteric-customtx');
    var sValueToSelect = sEntericValue;
    var nListIndex = -1;
    if(sValueToSelect !== undefined && sValueToSelect !== null && sValueToSelect !== -1)
    {
        bFoundSomething = true;
        activateCheckboxById(id_enteric);   //radioidroot + 'enteric');
        nListIndex = selectListItem(oMyList, sValueToSelect);
        if(nListIndex < 0)
        {
            //Convert control to custom text
            setAsCustomTextByName(sectionname + '_enteric_');
            oMyTextbox.value = sValueToSelect;
        }
    } else {
        //Clear the existing value.
        //clearListItem(oMyList);
        //oMyTextbox.value = '';
        var sRootName = sectionname + '_enteric_';
        resetComboInputByName(sRootName,true);
    }
    
    //IV
    var sListID = id_list_iv;   // idroot + 'iv-id';
    var oMyList=document.getElementById(sListID);
    var oMyTextbox=document.getElementById(id_text_iv); // idroot + 'iv-customtx');
    var sValueToSelect = sIVValue;
    var nListIndex = -1;
    if(sValueToSelect !== undefined && sValueToSelect !== null && sValueToSelect !== -1)
    {
        bFoundSomething = true;
        activateCheckboxById(id_iv);    //radioidroot + 'iv');
        selectListItem(oMyList, sValueToSelect);
        if(nListIndex < 0)
        {
            //Convert control to custom text
            setAsCustomTextByName(sectionname + '_iv_');
            oMyTextbox.value = sValueToSelect;
        }
    } else {
        //Clear the existing value.
        var sRootName = sectionname + '_iv_';
        resetComboInputByName(sRootName,true);
    }

    if(!bFoundSomething)    //20140808
    {
        activateCheckboxById(id_none);  //radioidroot + 'none');    //20140724 Important that we do this at least once.     
    }

    //Require acknowledgement.
    setAckRequiredForSection(sectionname);

    //Now make sure the right things are enabled, disabled.
    setEnabledDisabledInCheckboxTypeSection(sectionname);
    
//    alert('Done setDefaultInCheckboxTypeSection for ['+sectionname+']');
}

function setBlanksInRadioTypeSection(sectionname, ackid)
{
    doit = confirm('Any currently selected values in the ' + sectionname + ' section will be cleared if you continue.\n\nContinue with value clearing?');
    if(doit)
    {
        if(ackid)
        {
            var checkbox = document.getElementById(ackid);
            checkbox.checked = false;
        }
        clearValuesInSection(sectionname);
    }
    return doit;
}

function setBlanksInCheckboxTypeSection(sectionname, ackid)
{
    doit = confirm('Any currently selected values in the ' + sectionname + ' section will be cleared if you continue.\n\nContinue with value clearing?');
    if(doit)
    {
        if(ackid)
        {
            var checkbox = document.getElementById(ackid);
            checkbox.checked = false;
        }
        clearValuesInSection(sectionname);
    }
    return doit;
}

/**
 * Call this to set the enabled/disable based on checkbox selections.
 * @param {type} sectionname
 * @returns {undefined}
 */
function setEnabledDisabledInCheckboxTypeSection(sectionname)
{
    var id_map = getProtocolControlIDMap();
    var id_none = id_map[sectionname]['checkbox']['id_none'];
    var id_enteric = id_map[sectionname]['checkbox']['id_enteric'];
    var id_iv = id_map[sectionname]['checkbox']['id_iv'];
    var id_list_enteric = id_map[sectionname]['list']['id_enteric'];
    var id_text_enteric = id_map[sectionname]['text']['id_enteric'];
    var id_list_iv = id_map[sectionname]['list']['id_iv'];
    var id_text_iv = id_map[sectionname]['text']['id_iv'];
    
    var oChkNone=document.getElementById(id_none);  //chkid_none);
    var oChkE=document.getElementById(id_enteric);  //chkid_enteric);
    var oChkI=document.getElementById(id_iv);   //chkid_iv);

    //var sEListID = idroot + 'enteric-id';
    var oEList=document.getElementById(id_list_enteric);    //sEListID);
    var oETextbox=document.getElementById(id_text_enteric); //idroot + 'enteric-customtx');
    oEList.disabled = !oChkE.checked;
    oETextbox.disabled = !oChkE.checked;

    //var sIVListID = idroot + 'iv-id';
    var oIVList=document.getElementById(id_list_iv);    //sIVListID);
    var oIVTextbox=document.getElementById(id_text_iv);    //idroot + 'iv-customtx');
    oIVList.disabled = !oChkI.checked;
    oIVTextbox.disabled = !oChkI.checked;
    
    if(!oChkE.checked && !oChkI.checked)
    {
        oChkNone.checked = true;
    } else {
        oChkNone.checked = false;
    }
}


function isArray(myArray) 
{
    return myArray.constructor.toString().indexOf("Array") > -1;
}

/**
 * Call this to reset the values of a section back to their defaults
 */
function setDefaultValuesInSection(sectionname, aTemplateData)
{
//alert('INTO debug look in ' + sectionname + ' set default values!-->' + JSON.stringify(aTemplateData));
    var id_map = getProtocolControlIDMap();
    var id_ackgrp = id_map[sectionname]['acknowledge']['group'];
    var id_ackchk = id_map[sectionname]['acknowledge']['checkbox'];
    var id_ackreq = id_map[sectionname]['acknowledge']['required'];
    
    if(sectionname === 'hydration')
    {
        clearValuesInSection(sectionname);
        if(aTemplateData.hydration !== -1)
        {
            //Set it to the values in template data.
            var sOralValue = aTemplateData.hydration.oral;
            var sIVValue = aTemplateData.hydration.iv;
            setDefaultInRadioTypeSection(sectionname, id_ackchk, sOralValue, sIVValue);            
        }
    } else
    if(sectionname === 'sedation')
    {
        clearValuesInSection(sectionname);
        if(aTemplateData.sedation !== -1)
        {
            //Set it to the values in template data.
            var sOralValue = aTemplateData.sedation.oral;
            var sIVValue = aTemplateData.sedation.iv;
            setDefaultInRadioTypeSection(sectionname, id_ackchk, sOralValue, sIVValue);            
        }
    } else 
    if(sectionname === 'contrast')
    {
        clearValuesInSection(sectionname);
        if(aTemplateData.contrast !== -1)
        {
            //Set it to the values in template data.
            var sEntericValue = aTemplateData.contrast.enteric;
            var sIVValue = aTemplateData.contrast.iv;
            setDefaultInCheckboxTypeSection(sectionname, id_ackchk, sEntericValue, sIVValue);            
        }
    } else 
    if(sectionname === 'radioisotope') //20140723
    {
        clearValuesInSection(sectionname);
        if(aTemplateData.radioisotope !== -1)
        {
            //Set it to the values in template data.
            var sEntericValue = aTemplateData.radioisotope.enteric;
            var sIVValue = aTemplateData.radioisotope.iv;
            setDefaultInCheckboxTypeSection(sectionname, id_ackchk, sEntericValue, sIVValue);            
        }
    } else
    if(sectionname === 'consentreq')
    {
        if(aTemplateData.consentreq === -1)
        {
            //Clear it all.
            clearValuesInSection(sectionname);
        } else {
            //Set it to the values in template data.
            var sValueToSelect = aTemplateData.consentreq;
            if(sValueToSelect !== undefined && sValueToSelect !== null)
            {
                var id_val = id_map[sectionname]['radio']['id_'+sValueToSelect];
                activateRadioButtonById(id_val);
            }

            //Require acknowledgement.
            setAckRequiredForSection(sectionname);
        }
    } else
    if(sectionname === 'protocolnotes')
    {
        if(aTemplateData.protocolnotes === -1)
        {
            //Clear it all.
            clearValuesInSection(sectionname);
        } else {
            //Set it to the values in template data.
            var sValue = aTemplateData.protocolnotes.text;
            if(sValue !== undefined && sValue !== null)
            {
                var id_real = id_map[sectionname]['textarea']['id'];
                var textbox = document.getElementById(id_real);
                textbox.value = sValue;
            }

            //Require acknowledgement.
            setAckRequiredForSection(sectionname);
        }
    } else
    if(sectionname === 'examnotes')
    {
        if(aTemplateData.examnotes === -1)
        {
            //Clear it all.
            clearValuesInSection(sectionname);
        } else {
            //Set it to the values in template data.
            var sValue = aTemplateData.examnotes.text;
            if(sValue !== undefined && sValue !== null)
            {
                var id_real = id_map[sectionname]['textarea']['id'];
                var textbox = document.getElementById(id_real);
                textbox.value = sValue;
            }

            //Require acknowledgement.
            setAckRequiredForSection(sectionname);
        }
    }
//alert('OUTOF debug look in ' + sectionname + ' set default values!-->' + JSON.stringify(aTemplateData));
}

/**
 * Clear all the values in the section.
 */
function clearValuesInSection(sectionname)
{
    var id_map = getProtocolControlIDMap();
    
    //Start by declaring that we do not have default values selected.
    notDefaultValuesInSection(sectionname);    

    //Now clear all the controls in the section.
    if(sectionname === 'hydration')
    {
        var ackid = id_map[sectionname]['acknowledge']['checkbox'];
        setDefaultInRadioTypeSection(sectionname, ackid, '', '');
        
        setAsPickFromListByName('hydration_oral_');
        setAsPickFromListByName('hydration_iv_');
        
        var id_none = id_map[sectionname]['radio']['id_none'];
        activateRadioButtonById(id_none);

    } else
    if(sectionname === 'sedation')
    {
        var ackid = id_map[sectionname]['acknowledge']['checkbox'];
        setDefaultInRadioTypeSection(sectionname, ackid, '', '');
        
        setAsPickFromListByName('sedation_oral_');
        setAsPickFromListByName('sedation_iv_');
        
        var id_none = id_map[sectionname]['radio']['id_none'];
        activateRadioButtonById(id_none);
    } else
    if(sectionname === 'contrast')
    {
        var ackid = id_map[sectionname]['acknowledge']['checkbox'];
        setDefaultInCheckboxTypeSection(sectionname, ackid, '', '');
        
        var id_none = id_map[sectionname]['checkbox']['id_none'];
        var id_enteric = id_map[sectionname]['checkbox']['id_enteric'];
        var id_iv = id_map[sectionname]['checkbox']['id_iv'];
        clearCheckboxById(id_enteric);
        clearCheckboxById(id_iv);
        activateCheckboxById(id_none);
    } else
    if(sectionname === 'consentreq')
    {
        var id_unknown = id_map[sectionname]['checkbox']['id_unknown'];
        activateRadioButtonById(id_unknown);
        
        var radiobtn = document.getElementById(id_unknown);
        radiobtn.checked = false;   //Now uncheck it too       
    } else
    if(sectionname === 'radioisotope' || sectionname === 'contrast')
    {
        var ackid = id_map[sectionname]['acknowledge']['checkbox'];
        setDefaultInCheckboxTypeSection(sectionname, ackid, '', '');
        
        var id_none = id_map[sectionname]['checkbox']['id_none'];
        var id_enteric = id_map[sectionname]['checkbox']['id_enteric'];
        var id_iv = id_map[sectionname]['checkbox']['id_iv'];
        clearCheckboxById(id_enteric);
        clearCheckboxById(id_iv);
        activateCheckboxById(id_none);
    } else
    if(sectionname === 'protocolnotes')
    {
        var id_textarea = id_map[sectionname]['textarea']['id'];
        var textbox = document.getElementById(id_textarea);
        textbox.value = '';
    } else
    if(sectionname === 'examnotes')
    {
        var id_textarea = id_map[sectionname]['textarea']['id'];
        var textbox = document.getElementById(id_textarea);
        textbox.value = '';
    }
}

/**
 * Pass in the ID of the radio button
 * @param ID mainradioid
 */
function activateRadioButtonById(mainradioid)
{
    var radiobtn = document.getElementById(mainradioid);
    if(radiobtn.disabled)
    {
        radiobtn.checked = true;
    } else {
        //Only works when not disabled.
        $( "#" + mainradioid ).trigger( "click" );  //So it cascades the event
    }
}

/**
 * Pass in the ID of the checkbox
 * @param ID mainradioid
 */
function activateCheckboxById(chkid)
{
    var chkbox = document.getElementById(chkid);
    if(chkbox.disabled)
    {
        chkbox.checked = true;
    } else {
        //Only works when not disabled.
        $( "#" + chkid ).trigger( "click" );  //So it cascades the event
    }
}

/**
 * Pass in the ID of the radio button that has no other controls activated by it.
 * @param ID mainradioid
 */
function clearRadioGroup(mainradioid)
{
    activateRadioButtonById(mainradioid);
    var radiobtn = document.getElementById(mainradioid);
    radiobtn.checked = false;   //Now uncheck it too       
}

/**
 * Clear a checkbox such that it triggers cascading events
 * @param ID checkboxid
 */
function clearCheckboxById(checkboxid)
{
    //alert('clearing ' + checkboxid);
    var checkbox = document.getElementById(checkboxid);
    if(checkbox.disabled)
    {
        checkbox.checked = false;
    } else {
        //This only works if checkbox is not disabled.
        checkbox.checked = true;   //Turn it on first     
        $( "#" + checkboxid ).trigger( "click" );  //So it cascades the event
    }
    //alert('cleared ' + checkboxid);
}

/**
 * Pass in the NAME of the radio buttons group
 * @param sRadioGroupName 
 */
function disableRadioGroupByName(sRadioGroupName)
{
    var oRadios=document.getElementsByName(sRadioGroupName);
    for (var i = 0; i< oRadios.length;  i++)
    {
        oRadios[i].disabled = true;
    }
}

/**
 * Pass in the NAME of the radio buttons group
 * @param sRadioGroupName 
 */
function enableRadioGroupByName(sRadioGroupName)
{
    var oRadios=document.getElementsByName(sRadioGroupName);
    for (var i = 0; i< oRadios.length;  i++)
    {
        oRadios[i].disabled = false;
    }
}

/**
 * Pass in the IDs
 */
function disable3CheckboxesByIds(sID1,sID2,sID3)
{
    var checkbox = document.getElementById(sID1);
    checkbox.disabled = true;
    var checkbox = document.getElementById(sID2);
    checkbox.disabled = true;
    var checkbox = document.getElementById(sID3);
    checkbox.disabled = true;
}

/**
 * Pass in the IDs
 */
function enable3CheckboxesByIds(sID1,sID2,sID3)
{
    var checkbox = document.getElementById(sID1);
    checkbox.disabled = false;
    var checkbox = document.getElementById(sID2);
    checkbox.disabled = false;
    var checkbox = document.getElementById(sID3);
    checkbox.disabled = false;
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
 */
function notDefaultValuesInSection(sectionname, oGrp)
{
    if(oGrp === undefined)
    {
        var grpmatch = "div[id^='edit-default-values-grp-"+sectionname+"']";
        var sID = $(grpmatch).attr('id');
        var oGrp=document.getElementById(sID);
        if (oGrp) {
            oGrp.style.display = 'none';
        }
    }
    
    var sName = 'require_acknowledgement_for_' + sectionname;
    var oTB=document.getElementsByName(sName);
    oTB[0].value = 'no';
}

/**
 * Call this whenever a checkbox that is part of a set of checkboxes gets clicked.
 */
function syncCheckboxesAndComboboxes(sectionname, triggeringControl)
{
    var id_map = getProtocolControlIDMap();
    var sNoneID = id_map[sectionname]['checkbox']['id_none'];
    var sEntericID = id_map[sectionname]['checkbox']['id_enteric'];
    var sIVID = id_map[sectionname]['checkbox']['id_iv'];

    //If they clicked None, then clear the other boxes NOW otherwise gets reverted later.
    if(triggeringControl.id == sNoneID)
    {
        if(triggeringControl.checked)
        {
            var ackid = id_map[sectionname]['acknowledge']['checkbox'];
            var didit = setBlanksInCheckboxTypeSection(sectionname, ackid);
            if(didit)
            {
                //Clear the other checkboxes now.
                clearCheckboxById(sEntericID);
                clearCheckboxById(sIVID);
                var sRootName = sectionname + '_enteric_';
                resetComboInputByName(sRootName,true);
                var sRootName = sectionname + '_iv_';
                resetComboInputByName(sRootName,true);
            }
        }
    } else {
        if(triggeringControl.checked)
        {
            //Clear the NONE checkbox now.
            clearCheckboxById(sNoneID);
        }
    }
    
    //Now make sure the right things are enabled, disabled.
    setEnabledDisabledInCheckboxTypeSection(sectionname);
}

/**
 * Call this when no longer using default values in a checkbox section
 */
function notDefaultValuesInSectionAndSetCheckboxes(sectionname,triggeringControl)
{
    notDefaultValuesInSection(sectionname);
    syncCheckboxesAndComboboxes(sectionname, triggeringControl);
    return;
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

    $('#administer-modal')
        .html(msg)
        .dialog({
            title: 'Contraindication explanation',
            buttons: {
                'OK': function () {
                    $(this).dialog('close');
                }
            }
        });
}

function copyValueFromSourceToTarget(oSourceControl,oTargetControl)
{
    oTargetControl.value = oSourceControl.value;
}