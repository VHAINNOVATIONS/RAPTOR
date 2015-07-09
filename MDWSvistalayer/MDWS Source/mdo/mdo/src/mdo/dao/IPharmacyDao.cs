using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IPharmacyDao
    {
        //Medication[] getOutpatientMeds(string fromDate, string toDate, int nRex);
        Medication[] getOutpatientMeds();
        Medication[] getIvMeds();
        Medication[] getIvMeds(string pid);
        Medication[] getUnitDoseMeds();
        Medication[] getUnitDoseMeds(string pid);
        Medication[] getOtherMeds();
        Medication[] getOtherMeds(string pid);
        Medication[] getAllMeds();
        Medication[] getAllMeds(string dfn);
        Medication[] getVaMeds(string dfn);
        Medication[] getVaMeds();
        Medication[] getInpatientForOutpatientMeds();
        Medication[] getInpatientForOutpatientMeds(string pid);
        string getMedicationDetail(string medId);
        string getOutpatientRxProfile();
        string getMedsAdminHx(string fromDate, string toDate, int nrpts);
        string getMedsAdminLog(string fromDate, string toDate, int nrpts);
        string getImmunizations(string fromDate, string toDate, int nrpts);
        Medication refillPrescription(string rxId);
    }
}
