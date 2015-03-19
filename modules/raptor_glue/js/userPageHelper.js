/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */


function initializePrivilegeControls(role_nm, all_role_rights, bLockAll)
{
    //alert('starting with javascript thing');

    var oFieldSet;
    var oFieldPasswordWrapper = document.getElementById('edit-password');
    var oFieldPassword1 = document.getElementById('edit-password-pass1');
    var oFieldPassword2 = document.getElementById('edit-password-pass2');
    var oFieldSetWP = document.getElementById('edit-worklistpref');
    var oFieldSetKWWP = document.getElementById('edit-keywords');
    var oFieldSetCP = document.getElementById('edit-collaborationpref');
    var oFormMode = document.getElementsByName('formmode')[0];

    //alert('mid1 with javascript thing');

    //alert('the selected role is=' + role_nm);
    if(!(role_nm in all_role_rights))
    {
        //Nothing to do because the role_nm provided is not in the array.
        oFieldPasswordWrapper.style.display = 'none';
        oFieldSetWP.style.display = 'none';
        oFieldSetCP.style.display = 'none';
        //alert('mid1a with javascript thing');
        return; //This can happen on add user form.
    }
    //alert('mid2 with javascript thing');
    
    //alert('we are in ' + oFormMode.value + ' mode');
    if(oFormMode.value === 'A')
    {
        //Add mode.
        if(role_nm !== 'Site Administrator')
        {
            //Hide the password.
            var sRandomLargePassword = '123123123123123123123123';  //TODO --- change this at runtime!!!!
            oFieldPassword1.value = sRandomLargePassword;    
            oFieldPassword2.value = sRandomLargePassword;
            oFieldPasswordWrapper.style.display = 'none';

        } else {
            //Show the password.
            //alert('we will show the password');
            oFieldPassword1.value = '';
            oFieldPassword2.value = '';
            if(oFieldPasswordWrapper.style.display !== 'block')
            {
                oFieldPasswordWrapper.style.display = 'block';
            }
        }
    } else {
        //Hide the password.
        //alert('we will NOT show the password');
        var sRandomLargePassword = '123123123123123123123123';  //TODO --- change this at runtime!!!!
        oFieldPassword1.value = sRandomLargePassword;    
        oFieldPassword2.value = sRandomLargePassword;
        oFieldPasswordWrapper.style.display = 'none';
    }
    var rr = all_role_rights[role_nm];
    //alert('the role rights of ' + role_nm + '=' + rr.CEUA1 + ' locked=' + rr.lockCEUA1);
    //alert('role stuff...' + JSON.stringify(rr));
    
    //The ticket control privileges
    var show = 0;
    show += initOnePrivControl(document.getElementsByName('SWI1')[0], rr.SWI1, rr.lockSWI1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('PWI1')[0], rr.PWI1, rr.lockPWI1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('APWI1')[0], rr.APWI1, rr.lockAPWI1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('SUWI1')[0], rr.SUPWI1, rr.lockSUWI1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('CE1')[0], rr.CE1, rr.lockCE1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('QA1')[0], rr.QA1, rr.lockQA1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('SP1')[0], rr.SP1, rr.lockSP1, false, bLockAll);
    
    oFieldSet = document.getElementById('edit-ticketmgtprivileges');
    if(show === 0)
    {
        //Hide it.
        oFieldSet.style.display = 'none';
    } else {
        //Show it.
        if(oFieldSet.style.display !== 'block')
        {
            oFieldSet.style.display = 'block';
        }
    }

    //The account controls
    show = 0;
    show += initOnePrivControl(document.getElementsByName('CEUA1')[0], rr.CEUA1, rr.lockCEUA1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('LACE1')[0], rr.LACE1, rr.lockLACE1, false, bLockAll);
    oFieldSet = document.getElementById('edit-accountmgtprivileges');
    if(show === 0)
    {
        //Hide it.
        oFieldSet.style.display = 'none';
    } else {
        //Show it.
        if(oFieldSet.style.display !== 'block')
        {
            oFieldSet.style.display = 'block';
        }
    }

    //The site wide privileges
    show = 0;
    //show += initOnePrivControl(document.getElementsByName('VREP1')[0], rr.VREP1, rr.lockVREP1, false);
    //show += initOnePrivControl(document.getElementsByName('VREP2')[0], rr.VREP2, rr.lockVREP2, false);
    show += initOnePrivControl(document.getElementsByName('EBO1')[0], rr.EBO1, rr.lockEBO1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('UNP1')[0], rr.UNP1, rr.lockUNP1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('REP1')[0], rr.REP1, rr.lockREP1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('DRA1')[0], rr.DRA1, rr.lockDRA1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('ELCO1')[0], rr.ELCO1, rr.lockELCO1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('ELHO1')[0], rr.ELHO1, rr.lockELHO1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('ELSO1')[0], rr.ELSO1, rr.lockELSO1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('ELSVO1')[0], rr.ELSVO1, rr.lockELSVO1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('ELRO1')[0], rr.ELRO1, rr.lockELRO1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('ECIR1')[0], rr.ECIR1, rr.lockECIR1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('EECC1')[0], rr.EECC1, rr.lockEECC1, false, bLockAll);
    show += initOnePrivControl(document.getElementsByName('EERL1')[0], rr.EERL1, rr.lockEERL1, false, bLockAll);
    //show += initOnePrivControl(document.getElementsByName('EARM1')[0], rr.EARM1, rr.lockEARM1, false);
    show += initOnePrivControl(document.getElementsByName('CUT1')[0], rr.CUT1, rr.lockCUT1, false, bLockAll);
    oFieldSet = document.getElementById('edit-sitewideconfig');
    if(show === 0)
    {
        //Hide it.
        oFieldSet.style.display = 'none';
    } else {
        //Show it.
        if(oFieldSet.style.display !== 'block')
        {
            oFieldSet.style.display = 'block';
        }
    }
    
    //Show/Hide the prefs sections now.
    if(role_nm == 'Radiologist')
    {
        oFieldSetWP.style.display = 'block';
        oFieldSetKWWP.style.display = 'block';
        oFieldSetCP.style.display = 'block';
    } else {
        if(role_nm == 'Resident')
        {
            oFieldSetWP.style.display = 'block';
            oFieldSetKWWP.style.display = 'block';
            oFieldSetCP.style.display = 'none';
        } else {
            if(role_nm == 'Technologist')
            {
                oFieldSetWP.style.display = 'block';
                oFieldSetKWWP.style.display = 'none';
                oFieldSetCP.style.display = 'none';
            } else {
                oFieldSetWP.style.display = 'none';
                oFieldSetCP.style.display = 'none';
            }
        }
    }
    
    //alert('done with javascript thing');
    
}

function initOnePrivControl(oCB, nDefault, nLocked, bAlwaysShow, bLockOverride)
{
    var show;
    var parentDiv = oCB.parentNode;
    oCB.checked = (nDefault == 1);  //DO NOT USE === here!!!!!!  Only use == not ===!!!!
    oCB.disabled = (bLockOverride || nLocked == 1);  //DO NOT USE === here!!!!!!  Only use == not ===!!!!
    if(oCB.disabled)
    {
        //Create a hidden field with the values so it gets submitted.
        var input = document.createElement("input");
        input.setAttribute("name", oCB.getAttribute("name"));
        input.setAttribute("type", "hidden");
        input.setAttribute("value", nDefault);
        parentDiv.appendChild(input);
    }
    if(!bAlwaysShow)
    {
        if(!oCB.checked && oCB.disabled)
        {
            //Don't bother showing the control at all.
            parentDiv.style.display = 'none';
            show = 0;
        } else {
            parentDiv.style.display = 'inline';
            show = 1;
        }
    }
    //alert('Look (value=' + nDefault + ')' + JSON.stringify(oCB))
    return show;
}
