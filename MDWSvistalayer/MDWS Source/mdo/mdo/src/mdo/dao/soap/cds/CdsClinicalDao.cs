using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.soap.cds
{
    public class CdsClinicalDao : IClinicalDao
    {
        CdsConnection _cxn;

        public CdsClinicalDao(CdsConnection cxn)
        {
            _cxn = cxn;
        }


        public string getAllergiesAsXML()
        {
            string allergiesFilter = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" +
                "<filter:filter vhimVersion=\"Vhim_4_00\"" +
                "	xmlns:filter=\"Filter\"" +
                "	xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">" +
                "	<filterId>IC_SINGLE_PATIENT_ALL_DATA_FILTER</filterId>" +
                "	<patients>" +
                "		<NationalId>" + _cxn.Pid + "</NationalId>" +
                "           <excludeIdentifiers>" +
                "               <assigningAuthority>USDOD</assigningAuthority>" +
                "           </excludeIdentifiers>" +
                "	</patients>" +
                "	<entryPointFilter queryName=\"MHV_ICQuery\">" +
                "		<domainEntryPoint>IntoleranceCondition</domainEntryPoint>" +
                "	</entryPointFilter>" +
                "</filter:filter>";
            string result = _cxn.Proxy.readClinicalData1("MHVIntoleranceConditionRead40011", allergiesFilter,
                "IC_SINGLE_PATIENT_ALL_DATA_FILTER", "MHV-REQUEST-ID-" + Guid.NewGuid().ToString());
            return result;
        }


        #region NotImplementedMembers

        public System.Collections.Hashtable getPatientRecord(string pid, string types)
        {
            throw new NotImplementedException();
        }

        public Allergy[] getAllergiesBySite(string siteCode)
        {
            throw new NotImplementedException();
        }

        public Allergy[] getAllergies()
        {
            throw new NotImplementedException();
        }

        public Problem[] getProblemList(string type)
        {
            throw new NotImplementedException();
        }

        public MdoDocument[] getHealthSummaryList()
        {
            throw new NotImplementedException();
        }

        public string getHealthSummaryTitle(string summaryId)
        {
            throw new NotImplementedException();
        }

        public string getHealthSummaryText(string mpiPid, MdoDocument hs, string sourceSiteId)
        {
            throw new NotImplementedException();
        }

        public HealthSummary getHealthSummary(MdoDocument hs)
        {
            throw new NotImplementedException();
        }

        public string getAdHocHealthSummaryByDisplayName(string displayName)
        {
            throw new NotImplementedException();
        }

        public SurgeryReport[] getSurgeryReports(bool fWithText)
        {
            throw new NotImplementedException();
        }

        public SurgeryReport[] getSurgeryReportsBySite(string siteCode)
        {
            throw new NotImplementedException();
        }

        public string getSurgeryReportText(string rptId)
        {
            throw new NotImplementedException();
        }

        public string getNhinData(string types = null)
        {
            throw new NotImplementedException();
        }

        public string getNhinData(string dfn, string types = null)
        {
            throw new NotImplementedException();
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient()
        {
            throw new NotImplementedException();
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient(string pid)
        {
            throw new NotImplementedException();
        }

        public void addMentalHealthInstrumentResultSet(MentalHealthInstrumentAdministration administration)
        {
            throw new NotImplementedException();
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatientBySurvey(string surveyName)
        {
            throw new NotImplementedException();
        }

        public List<MentalHealthInstrumentResultSet> getMentalHealthInstrumentResultSetsBySurvey(string surveyName)
        {
            throw new NotImplementedException();
        }

        public MentalHealthInstrumentResultSet getMentalHealthInstrumentResultSet(string administrationId)
        {
            throw new NotImplementedException();
        }

        public User[] getStaffByCriteria(string siteCode, string searchTerm, string firstName, string lastName, string type)
        {
            throw new NotImplementedException();
        }
        #endregion
    }
}
