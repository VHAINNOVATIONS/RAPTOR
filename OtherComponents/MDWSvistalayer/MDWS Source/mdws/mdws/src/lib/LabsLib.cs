using System;
using System.Web;
using System.Collections.Specialized;
using System.Collections.Generic;
using System.Collections;
using gov.va.medora.mdo;
using gov.va.medora.utils;
using gov.va.medora.mdo.api;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class LabsLib
    {
        MySession mySession;

        public LabsLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TaggedPatientArrays getPatientsWithUpdatedChemHemReports(string username, string pwd, string fromDate)
        {
            TaggedPatientArrays result = new TaggedPatientArrays();
            //if (String.IsNullOrEmpty(username) | String.IsNullOrEmpty(pwd) | String.IsNullOrEmpty(fromDate))
            //{
            //    result.fault = new FaultTO("Must supply all arguments");
            //}
            try
            {
                LabsApi api = new LabsApi();
                DataSource ds = new DataSource { ConnectionString = "Data Source=VHACDWa01.vha.med.va.gov;Initial Catalog=CDWWork;Trusted_Connection=true" }; // TODO - need to figure out how cxn string will be handled
                AbstractConnection cxn = new gov.va.medora.mdo.dao.sql.cdw.CdwConnection(ds);
                Dictionary<string, HashSet<string>> dict = api.getUpdatedChemHemReports(cxn, DateTime.Parse(fromDate));
                result.arrays = new TaggedPatientArray[dict.Keys.Count];
                int arrayCount = 0;

                foreach (string key in dict.Keys)
                {
                    TaggedPatientArray tpa = new TaggedPatientArray(key);
                    tpa.patients = new PatientTO[dict[key].Count];
                    int patientCount = 0;
                    foreach (string patientICN in dict[key])
                    {
                        PatientTO p = new PatientTO { mpiPid = patientICN };
                        tpa.patients[patientCount] = p;
                        patientCount++;
                    }
                    result.arrays[arrayCount] = tpa;
                    arrayCount++;
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TaggedChemHemRptArrays getChemHemReports(string fromDate, string toDate)
        {
            TaggedChemHemRptArrays result = new TaggedChemHemRptArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ChemHemReport.getChemHemReports(mySession.ConnectionSet, fromDate, toDate);
                result = new TaggedChemHemRptArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedChemHemRptArrays getChemHemReportsRdv(string fromDate, string toDate, int nrpts)
        {
            TaggedChemHemRptArrays result = new TaggedChemHemRptArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = ChemHemReport.getChemHemReports(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedChemHemRptArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedCytologyRptArrays getCytologyReports(string fromDate, string toDate, int nrpts)
        {
            TaggedCytologyRptArrays result = new TaggedCytologyRptArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                LabsApi api = new LabsApi();
                IndexedHashtable t = api.getCytologyReports(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedCytologyRptArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedMicrobiologyRptArrays getMicrobiologyReports(string fromDate, string toDate, int nrpts)
        {
            TaggedMicrobiologyRptArrays result = new TaggedMicrobiologyRptArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                LabsApi api = new LabsApi();
                IndexedHashtable t = api.getMicrobiologyReports(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedMicrobiologyRptArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedSurgicalPathologyRptArrays getSurgicalPathologyReports(string fromDate, string toDate, int nrpts)
        {
            TaggedSurgicalPathologyRptArrays result = new TaggedSurgicalPathologyRptArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                LabsApi api = new LabsApi();
                IndexedHashtable t = api.getSurgicalPathologyReports(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedSurgicalPathologyRptArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getBloodAvailabilityReports(string fromDate, string toDate, int nrpts)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                LabsApi api = new LabsApi();
                IndexedHashtable t = api.getBloodAvailabilityReport(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getBloodTransfusionReports(string fromDate, string toDate, int nrpts)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                LabsApi api = new LabsApi();
                IndexedHashtable t = api.getBloodTransfusionReport(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getBloodBankReports()
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
                LabsApi api = new LabsApi();
                IndexedHashtable t = api.getBloodBankReport(mySession.ConnectionSet);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getElectronMicroscopyReports(string fromDate, string toDate, int nrpts)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                LabsApi api = new LabsApi();
                IndexedHashtable t = api.getElectronMicroscopyReport(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getCytopathologyReports()
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
                LabsApi api = new LabsApi();
                IndexedHashtable t = api.getCytopathologyReport(mySession.ConnectionSet);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getAutopsyReports()
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
                LabsApi api = new LabsApi();
                IndexedHashtable t = api.getAutopsyReport(mySession.ConnectionSet);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TextTO getLrDfn(string dfn)
        {
            return getLrDfn(null, dfn);
        }

        public TextTO getLrDfn(string sitecode, string dfn)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (dfn == "")
            {
                result.fault = new FaultTO("Missing dfn");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                LabsApi api = new LabsApi();
                string lrdfn = api.getLrDfn(cxn, dfn);
                result = new TextTO(lrdfn);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedLabTestArrays getLabTests(string target)
        {
            TaggedLabTestArrays result = new TaggedLabTestArrays();

            if (MdwsUtils.isAuthorizedConnection(mySession) != "OK")
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                LabsApi api = new LabsApi();
                return new TaggedLabTestArrays(api.getTests(mySession.ConnectionSet, target));
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TaggedTextArray getTestDescription(string identifierString)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (MdwsUtils.isAuthorizedConnection(mySession) != "OK")
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                LabsApi api = new LabsApi();
                return new TaggedTextArray(api.getTestDescription(mySession.ConnectionSet, identifierString));
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }
    }
}
