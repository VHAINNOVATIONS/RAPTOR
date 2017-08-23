using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    public class FhieClinicalDao : IClinicalDao
    {
        VistaConnection cxn = null;
        VistaClinicalDao vistaDao = null;

        public FhieClinicalDao(AbstractConnection cxn)
        {
            this.cxn = (VistaConnection)cxn;
            vistaDao = new VistaClinicalDao(cxn);
        }

        public Dictionary<string, ArrayList> getNoteTitles(String target, String direction)
        {
            return null;
        }

        public Allergy[] getAllergies()
        {
            return vistaDao.getAllergies();
        }

        public Problem[] getProblemList(string type)
        {
            return vistaDao.getProblemList(type);
        }

        #region Not Implemented
        public MdoDocument[] getHealthSummaryList()
        {
            return null;
        }

        public String getHealthSummaryTitle(String summaryId)
        {
            return null;
        }

        public string getHealthSummaryText(String mpiPid, MdoDocument hs, String sourceSiteId)
        {
            return null;
        }

        public HealthSummary getHealthSummary(MdoDocument hs)
        {
            return null;
        }

        public SurgeryReport[] getSurgeryReports(bool fWithText)
        {
            return null;
        }

        public string getSurgeryReportText(string rptId)
        {
            return null;
        }

        public string getAdHocHealthSummaryByDisplayName(string displayName)
        {
            return null;
        }

        public string getNhinData(string types = null)
        {
            return null;
        }

        public string getNhinData(string dfn, string types = null)
        {
            return null;
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient()
        {
            return null;
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient(string dfn)
        {
            return null;
        }

        public void addMentalHealthInstrumentResultSet(MentalHealthInstrumentAdministration administration)
        {
            return;
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatientBySurvey(string surveyName)
        {
            throw new NotImplementedException();
        }

        public MentalHealthInstrumentResultSet getMentalHealthInstrumentResultSet(string administrationId)
        {
            return null;
        }

        public List<MentalHealthInstrumentResultSet> getMentalHealthInstrumentResultSetsBySurvey(string surveyName)
        {
            throw new NotImplementedException();
        }

        public string getAllergiesAsXML()
        {
            throw new NotImplementedException();
        }


        public Hashtable getPatientRecord(string pid, string types)
        {
            throw new NotImplementedException();
        }

        public Allergy[] getAllergiesBySite(string siteCode)
        {
            throw new NotImplementedException();
        }

        public SurgeryReport[] getSurgeryReportsBySite(string siteCode)
        {
            throw new NotImplementedException();
        }

        

        #region Clinic Directory

        public User[] getStaffByCriteria(string siteCode, string searchTerm, string firstName, string lastName, string type)
        {
            throw new NotImplementedException();
        }

        #endregion

        #endregion






        
    }
}