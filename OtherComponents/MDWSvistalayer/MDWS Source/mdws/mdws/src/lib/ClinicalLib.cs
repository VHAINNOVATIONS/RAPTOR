using System;
using System.Collections.Specialized;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo.api;
using gov.va.medora.mdo;
using System.Collections.Generic;

namespace gov.va.medora.mdws
{
    public class ClinicalLib
    {
        MySession mySession;

        public ClinicalLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TaggedTextArray getAdHocHealthSummaryByDisplayName(string displayName)
        {
            TaggedTextArray result = new TaggedTextArray();

            string msg = MdwsUtils.isAuthorizedConnection(mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (displayName == "")
            {
                result.fault = new FaultTO("Missing displayName");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getAdHocHealthSummaryByDisplayName(mySession.ConnectionSet, displayName);
                return new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
                return result;
            }
        }

        public TextTO getAdHocHealthSummaryByDisplayName(string sitecode, string displayName)
        {
            TextTO result = new TextTO();
            if (displayName == "")
            {
                result.fault = new FaultTO("Missing display name");
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                result.text = ClinicalApi.getAdHocHealthSummaryByDisplayName(mySession.ConnectionSet.getConnection(sitecode), displayName);
                if (result.text == null)
                {
                    result.fault = new FaultTO("Site " + sitecode + " does not have " + displayName + " enabled! Please contact the site for remediation.");
                }
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

#if false
        public HealthSummaryTO getHealthSummary(string healthSummaryId, string healthSummaryName)
        {
            HealthSummaryTO result = new HealthSummaryTO();
            if (string.IsNullOrEmpty(healthSummaryId) && string.IsNullOrEmpty(healthSummaryName))
            {
                result.fault = new FaultTO("Missing health summary Id OR health summary name. Please provide one of the parameters.");
                return result;
            }

            try
            {
                HealthSummary hs = ClinicalApi.getHealthSummary(mySession.ConnectionSet.BaseConnection, new MdoDocument(healthSummaryId, healthSummaryName));
                result.Init(hs);
            }
            catch (Exception e)
            {

                result.fault = new FaultTO(e.Message);
            }
            return result;
        } 
#endif
        public TaggedHealthSummaryArray getHealthSummary(string healthSummaryId, string healthSummaryName)
        {
            TaggedHealthSummaryArray result = new TaggedHealthSummaryArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
                return result;
            }
            if ((mySession.Patient == null) || (string.IsNullOrEmpty(mySession.Patient.LocalPid)))
            {
                result.fault = new FaultTO("Need to select a patient before calling this method.");
                return result;
            }
            if (string.IsNullOrEmpty(healthSummaryId) && string.IsNullOrEmpty(healthSummaryName))
            {
                result.fault = new FaultTO("Missing health summary Id OR health summary name. Please provide one of the parameters.");
                return result;
            }

            try
            {
                IndexedHashtable hs = ClinicalApi.getHealthSummary(mySession.ConnectionSet, new MdoDocument(healthSummaryId, healthSummaryName));
                result.Init(hs);
            }
            catch (Exception e)
            {

                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedAllergyArrays getAllergies()
        {
            TaggedAllergyArrays result = new TaggedAllergyArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getAllergies(mySession.ConnectionSet);
                result = new TaggedAllergyArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedAllergyArrays getAllergiesBySite(string siteCode)
        {
            TaggedAllergyArrays result = new TaggedAllergyArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getAllergiesBySite(mySession.ConnectionSet, siteCode);
                result = new TaggedAllergyArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedRadiologyReportArrays getRadiologyReportsBySite(string fromDate, string toDate, string siteCode)
        {
            TaggedRadiologyReportArrays results = new TaggedRadiologyReportArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                results.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(fromDate))
            {
                results.fault = new FaultTO("Missing fromDate");
            }
            else if (String.IsNullOrEmpty(toDate))
            {
                results.fault = new FaultTO("Missing toDate");
            }
            else if (String.IsNullOrEmpty(siteCode))
            {
                results.fault = new FaultTO("missing siteCode");
            }
            if (results.fault != null)
            {
                return results;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getRadiologyReportsBySite(mySession.ConnectionSet, fromDate, toDate, siteCode);
                results = new TaggedRadiologyReportArrays(t);
            }
            catch (Exception e)
            {
                results.fault = new FaultTO(e);
            }

            return results;
        }


        public TaggedRadiologyReportArrays getRadiologyReports(string fromDate, string toDate, int nrpts)
        {
            TaggedRadiologyReportArrays result = new TaggedRadiologyReportArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(fromDate))
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (String.IsNullOrEmpty(toDate))
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getRadiologyReports(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedRadiologyReportArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedSurgeryReportArrays getSurgeryReports()
        {
            return getSurgeryReports(false);
        }

        public TaggedSurgeryReportArrays getSurgeryReports(bool fWithText)
        {
            TaggedSurgeryReportArrays result = new TaggedSurgeryReportArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getSurgeryReports(mySession.ConnectionSet, fWithText);
                result = new TaggedSurgeryReportArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedSurgeryReportArrays getSurgeryReportsBySite(string siteCode)
        {
            TaggedSurgeryReportArrays result = new TaggedSurgeryReportArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getSurgeryReportsBySite(mySession.ConnectionSet, siteCode);
                result = new TaggedSurgeryReportArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TextTO getSurgeryReportText(string siteId, string rptId)
        {
            TextTO result = new TextTO();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (siteId == "")
            {
                result.fault = new FaultTO("Missing siteId");
            }
            else if (rptId == "")
            {
                result.fault = new FaultTO("Missing rptId");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                ClinicalApi api = new ClinicalApi();
                string s = api.getSurgeryReportText(mySession.ConnectionSet.getConnection(siteId), rptId);
                result = new TextTO(s);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedProblemArrays getProblemList(string type)
        {
            TaggedProblemArrays result = new TaggedProblemArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (type == "")
            {
                result.fault = new FaultTO("Missing type");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getProblemList(mySession.ConnectionSet, type.ToUpper());
                result = new TaggedProblemArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedProblemArrays getFluRelatedProblemList()
        {
            TaggedProblemArrays result = new TaggedProblemArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getFluRelatedProblemList(mySession.ConnectionSet);
                result = new TaggedProblemArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public RadiologyReportTO getImagingReport(string ssn, string accessionNumber)
        {
            RadiologyReportTO result = new RadiologyReportTO();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(ssn))
            {
                result.fault = new FaultTO("Missing SSN");
            }
            else if (String.IsNullOrEmpty(accessionNumber))
            {
                result.fault = new FaultTO("Missing Accession Number");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                PatientApi patientApi = new PatientApi();
                Patient[] matches = patientApi.match(mySession.ConnectionSet.BaseConnection, ssn);

                if (matches == null || matches.Length != 1)
                {
                    result.fault = new FaultTO("More than one patient has that SSN in this site (" + 
                        mySession.ConnectionSet.BaseConnection.DataSource.SiteId.Id + ")");
                    return result;
                }
                RadiologyReport report = ImagingExam.getReportText(mySession.ConnectionSet.BaseConnection, matches[0].LocalPid, accessionNumber);
                
                result = new RadiologyReportTO(report);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }

            return result;
        }

        public TextTO getConsultNote(string consultId)
        {
            return getConsultNote(mySession.ConnectionSet.BaseConnection.DataSource.SiteId.Id, consultId);
        }

        public TextTO getConsultNote(string siteId, string consultId)
        {
            TextTO result = new TextTO();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(siteId))
            {
                result.fault = new FaultTO("Missing siteId");
            }
            else if (String.IsNullOrEmpty(consultId))
            {
                result.fault = new FaultTO("Missing consultId");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                string s = Consult.getConsultNote(mySession.ConnectionSet.getConnection(siteId), consultId);
                result = new TextTO(s);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getNhinData(string types)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ClinicalApi.getNhinData(mySession.ConnectionSet, types, 
                    mySession.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.MDWS_CONFIG_SECTION][conf.MdwsConfigConstants.NHIN_TYPES]);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedMentalHealthInstrumentAdministrationArrays getMentalHealthInstrumentsForPatient()
        {
            TaggedMentalHealthInstrumentAdministrationArrays result = new TaggedMentalHealthInstrumentAdministrationArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = MentalHealthInstrumentAdministration.getMentalHealthInstrumentsForPatient(mySession.ConnectionSet);
                result = new TaggedMentalHealthInstrumentAdministrationArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }


        public TaggedMentalHealthInstrumentAdministrationArrays getMentalHealthInstrumentsForPatientBySurvey(string surveyName)
        {
            TaggedMentalHealthInstrumentAdministrationArrays result = new TaggedMentalHealthInstrumentAdministrationArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = MentalHealthInstrumentAdministration.getMentalHealthInstrumentsForPatientBySurvey(mySession.ConnectionSet, surveyName);
                result = new TaggedMentalHealthInstrumentAdministrationArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }


        public MentalHealthInstrumentResultSetTO getMentalHealthInstrumentResultSet(string siteId, string administrationId)
        {
            MentalHealthInstrumentResultSetTO result = new MentalHealthInstrumentResultSetTO();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(siteId))
            {
                result.fault = new FaultTO("Missing siteId");
            }
            else if (String.IsNullOrEmpty(administrationId))
            {
                result.fault = new FaultTO("Missing administrationId");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                MentalHealthInstrumentResultSet rs = MentalHealthInstrumentAdministration.getMentalHealthInstrumentResultSet(mySession.ConnectionSet.getConnection(siteId), administrationId);
                result = new MentalHealthInstrumentResultSetTO(rs);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedMentalHealthResultSetArray getMentalHealthInstrumentResultSetsBySurvey(string siteId, string surveyName)
        {
            TaggedMentalHealthResultSetArray result = new TaggedMentalHealthResultSetArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(surveyName))
            {
                result.fault = new FaultTO("Missing siteId");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                List<MentalHealthInstrumentResultSet> rs = MentalHealthInstrumentAdministration.getMentalHealthInstrumentResultSetsBySurvey(mySession.ConnectionSet.getConnection(siteId), surveyName);
                result = new TaggedMentalHealthResultSetArray(siteId, rs);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }

            return result;
        }

        #region Clinic Directory

        public TaggedUserArrays getStaffByCriteria(string siteCode, string searchTerm, string firstName, string lastName, string type)
        {
            TaggedUserArrays result = new TaggedUserArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connection not established or authorized");
            }
            else if (String.IsNullOrEmpty(type))
            {
                result.fault = new FaultTO("Missing type of search {email, phone, firstAndLast, firstOrLast}");
            }
            else if (String.IsNullOrEmpty(siteCode))
            {
                result.fault = new FaultTO("Missing siteCode");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable ht = ClinicalApi.getStaffByCriteria(mySession.ConnectionSet, siteCode, searchTerm, firstName, lastName, type);
                result = new TaggedUserArrays(ht);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }

            return result;
        }

        #endregion
    }

    
}
