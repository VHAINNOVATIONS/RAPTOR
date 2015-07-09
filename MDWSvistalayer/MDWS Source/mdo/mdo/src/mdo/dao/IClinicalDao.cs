using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IClinicalDao
    {
        Problem[] getProblemList(string type);
        string getAllergiesAsXML();
        Allergy[] getAllergies();
        Allergy[] getAllergiesBySite(string siteCode);
        MdoDocument[] getHealthSummaryList();
        string getHealthSummaryTitle(string summaryId);
        string getHealthSummaryText(string mpiPid, MdoDocument hs, string sourceSiteId);
        // string getHealthSummary(string dfn, MdoDocument hs);
        HealthSummary getHealthSummary(MdoDocument hs);
        string getAdHocHealthSummaryByDisplayName(string displayName);
        SurgeryReport[] getSurgeryReports(bool fWithText);
        SurgeryReport[] getSurgeryReportsBySite(string siteCode);
        string getSurgeryReportText(string rptId);
        string getNhinData(string types = null);
        string getNhinData(string dfn, string types = null);
        List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient();
        List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatientBySurvey(string surveyName);
        List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient(string pid);
        void addMentalHealthInstrumentResultSet(MentalHealthInstrumentAdministration administration);
        MentalHealthInstrumentResultSet getMentalHealthInstrumentResultSet(string administrationId);
        Hashtable getPatientRecord(string pid, string types);
        List<MentalHealthInstrumentResultSet> getMentalHealthInstrumentResultSetsBySurvey(string surveyName);
        User[] getStaffByCriteria(string siteCode, string searchTerm, string firstName, string lastName, string type);
    }
}
