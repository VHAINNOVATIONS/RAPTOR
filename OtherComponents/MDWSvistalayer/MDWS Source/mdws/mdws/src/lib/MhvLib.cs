using System;
using System.Web;
using System.Collections.Specialized;
using System.Collections;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo.api;
using gov.va.medora.mdo;
using gov.va.medora.utils;
//using gov.va.medora.mdo.dao.soap.cds;
using gov.va.medora.mdo.domain.ccr;
using gov.va.medora.mdo.dao.sql.cdw;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.dao;
using System.Collections.Generic;
using gov.va.medora.mdws.conf;
using gov.va.medora.mdo.dao.soap.cds;

namespace gov.va.medora.mdws
{
    public class MhvLib
    {
        MySession mySession;

        public MhvLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TextTO getHealthSummary(string pwd, string sitecode, string mpiPid, string displayName)
        {
            TextTO result = new TextTO();

            if (String.IsNullOrEmpty(sitecode))
            {
                result.fault = new FaultTO("Missing sitecode");
            }
            else if (mpiPid == "")
            {
                result.fault = new FaultTO("Missing mpiPid");
            }
            else if (displayName == "")
            {
                result.fault = new FaultTO("Missing displayName");
            }
            if (result.fault != null)
            {
                return result;
            }

            AccountLib acctLib = new AccountLib(mySession);
            try
            {
                // Visit as DoD user...
                SiteArray sites = acctLib.patientVisit(pwd, sitecode, mpiPid, false);
                if (sites.fault != null)
                {
                    result.fault = sites.fault;
                    return result;
                }

                // Get the labs...
                ClinicalLib clinicalLib = new ClinicalLib(mySession);
                result = clinicalLib.getAdHocHealthSummaryByDisplayName(sitecode, displayName);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            finally
            {
                mySession.close();
            }
            return result;
        }

        public TaggedChemHemRptArray getChemHemReportsByReportDateFromSite(
            string pwd, string sitecode, string mpiPid, string fromDate, string toDate)
        {
            TaggedChemHemRptArray result = new TaggedChemHemRptArray();

            if (String.IsNullOrEmpty(sitecode))
            {
                result.fault = new FaultTO("Missing sitecode");
            }
            else if (mpiPid == "")
            {
                result.fault = new FaultTO("Missing mpiPid");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (toDate == "")
            {
                toDate = DateTime.Now.ToString("yyyyMMdd");
            }

            AccountLib acctLib = new AccountLib(mySession);
            try
            {
                // Visit as DoD user...
                SiteArray sites = acctLib.patientVisit(pwd, sitecode, mpiPid, false);
                if (sites.fault != null)
                {
                    result.fault = sites.fault;
                    return result;
                }

                // Get the labs...
                ChemHemReport[] rpts = ChemHemReport.getChemHemReports(mySession.ConnectionSet.getConnection(sitecode), fromDate, toDate);
                result = new TaggedChemHemRptArray(sitecode, rpts);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            finally
            {
                mySession.close();
            }
            return result;
        }

        public TaggedAppointmentArray getAppointmentsFromSite(
            string pwd, string sitecode, string mpiPid)
        {
            TaggedAppointmentArray result = new TaggedAppointmentArray();

            if (String.IsNullOrEmpty(sitecode))
            {
                result.fault = new FaultTO("Missing sitecode");
            }
            else if (String.IsNullOrEmpty(mpiPid))
            {
                result.fault = new FaultTO("Missing mpiPid");
            }
            if (result.fault != null)
            {
                return result;
            }

            AccountLib acctLib = new AccountLib(mySession);
            try
            {
                // Visit as DoD user...
                SiteArray sites = acctLib.patientVisit(pwd, sitecode, mpiPid, false);
                if (sites.fault != null)
                {
                    result.fault = sites.fault;
                    return result;
                }

                // Get the labs...
                EncounterApi api = new EncounterApi();
                Appointment[] appts = api.getAppointments(mySession.ConnectionSet.getConnection(sitecode));
                for (int i = 0; i < appts.Length; i++)
                {
                    appts[i].Status = undecodeApptStatus(appts[i].Status);
                }
                result = new TaggedAppointmentArray(sitecode, appts);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            finally
            {
                mySession.close();
            }
            return result;
        }

        internal string undecodeApptStatus(string status)
        {
            if (status == "NO-SHOW")
            {
                return "N";
            }
            if (status == "CANCELLED BY CLINIC")
            {
                return "C";
            }
            if (status == "NO-SHOW & AUTO RE-BOOK")
            {
                return "NA";
            }
            if (status == "CANCELLED BY CLINIC & AUTO RE-BOOK")
            {
                return "CA";
            }
            if (status == "INPATIENT APPOINTMENT")
            {
                return "I";
            }
            if (status == "CANCELLED BY PATIENT")
            {
                return "PC";
            }
            if (status == "CANCELLED BY PATIENT & AUTO-REBOOK")
            {
                return "PCA";
            }
            if (status == "NO ACTION TAKEN")
            {
                return "NT";
            }
            return status;
        }

        public TextTO getAllergiesAsXML(string appPwd, string patientICN)
        {
            //return new TextTO() { fault = new FaultTO("No longer implemented") };
            TextTO result = new TextTO();

            if (mySession == null || mySession.SiteTable == null || mySession.SiteTable.getSite("201") == null ||
                mySession.SiteTable.getSite("201").Sources == null || mySession.SiteTable.getSite("201").Sources[0] == null)
            {
                result.fault = new FaultTO("No CDS endpoint (site 201) in sites file!");
            }
            if (result.fault != null)
            {
                return result;
            }

            CdsConnection cxn = new CdsConnection(mySession.SiteTable.getSite("201").Sources[0]);
            cxn.Pid = patientICN;
            CdsClinicalDao dao = new CdsClinicalDao(cxn);

            try
            {
                // TODO - validate app password
                result.text = dao.getAllergiesAsXML();
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public ContinuityOfCareRecord getImmunizations(string appPwd, string patientICN)
        {
            Immunizations imm = new Immunizations();
            ContinuityOfCareRecord ccr = new ContinuityOfCareRecord() { Body = new ContinuityOfCareRecordBody() };
            ccr.Body.Immunizations = new System.Collections.Generic.List<StructuredProductType>();
            imm = new CdwPharmacyDao(new CdwConnection(new DataSource() 
                { ConnectionString = "Data Source=VHACDWa01.vha.med.va.gov;Initial Catalog=CDWWork;Trusted_Connection=true", }) 
                { Pid = patientICN })
                .getImmunizations("", "");
            ccr.Body.Immunizations = imm.Immunization;
            return ccr;
        }

        public TextTO getHthVitalsAsXML(String appPwd, String icn)
        {
            TextTO result = new TextTO();

            if (mySession == null || mySession.SiteTable == null || mySession.SiteTable.getSite("201") == null ||
                mySession.SiteTable.getSite("201").Sources == null || mySession.SiteTable.getSite("201").Sources[0] == null)
            {
                result.fault = new FaultTO("No CDS endpoint (site 201) in sites file!");
            }
            if (result.fault != null)
            {
                return result;
            }

            CdsConnection cxn = new CdsConnection(mySession.SiteTable.getSite("201").Sources[0]);
            CdsVitalsDao dao = new CdsVitalsDao(cxn);

            try
            {
                result.text = dao.getHthVitals(icn); // function is probably ignoring these params
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TextTO getLabReportsAsXML(string appPwd, string patientICN, string fromDate, string toDate)
        {
            TextTO result = new TextTO();

            if (mySession == null || mySession.SiteTable == null || mySession.SiteTable.getSite("201") == null ||
                mySession.SiteTable.getSite("201").Sources == null || mySession.SiteTable.getSite("201").Sources[0] == null)
            {
                result.fault = new FaultTO("No CDS endpoint (site 201) in sites file!");
            }
            if (result.fault != null)
            {
                return result;
            }

            CdsConnection cxn = new CdsConnection(mySession.SiteTable.getSite("201").Sources[0]);
            cxn.Pid = patientICN;
            CdsLabsDao dao = new CdsLabsDao(cxn);

            try
            {
                // TODO - validate app password
                result.text = dao.getAllLabReports(fromDate, toDate, 0); // function is probably ignoring these params
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public LabResultTO[] getLabReports(string appPwd, string patientICN, string fromDate, string toDate)
        {
            LabResultTO[] result = new LabResultTO[1];
            result[0] = new LabResultTO() { fault = new FaultTO("Not implemented") };
            return result;
        }

        public PatientMedicalRecordTO getData(string pwd, string site, bool multiSite, string pid, string types)
        {
            // TODO - implement checking of types
            PatientMedicalRecordTO result = new PatientMedicalRecordTO();

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("Missing application password");
            }
            else if (String.IsNullOrEmpty(pid))
            {
                result.fault = new FaultTO("Must supply patient ID");
            }
            else if (String.IsNullOrEmpty(types))
            {
                result.fault = new FaultTO("Must supply types argument", "Use 'getDataTypes' call to discover currently supported data domains");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Dictionary<string, string> patientIens = null;

                // this section is all about finding the patient's remote sites
                if (String.IsNullOrEmpty(site))
                {
                    // TBD - Should CDW be hardcoded in here? Probably should refactor this out
                    using (CdwConnection cdwCxn = new CdwConnection(
                        new DataSource() { ConnectionString = mySession.MdwsConfiguration.CdwSqlConfig.ConnectionString },
                        mySession.MdwsConfiguration.CdwSqlConfig.RunasUser))
                    {
                        patientIens = new CdwPatientDao(cdwCxn).getTreatingFacilityIds(pid);
                    }
                    if (patientIens == null || patientIens.Count == 0)
                    {
                        result.fault = new FaultTO("Unable to locate that patient with only ICN", "Try specifying the site with the patient's ID");
                        return result;
                    }
                    IEnumerator enumerator = patientIens.Keys.GetEnumerator();
                    enumerator.MoveNext();
                    site = enumerator.Current as string; // set the site to the first one in the list
                }
                else
                {
                    patientIens = new Dictionary<string, string>();
                    patientIens.Add(site, pid);
                }

                // check to be sure we're asking for Vista or CDW data
                Site selectedSite = mySession.SiteTable.getSite(site);

                if (selectedSite == null || selectedSite.Sources == null || selectedSite.Sources.Length == 0 ||
                    (selectedSite.getDataSourceByModality("HIS") == null && selectedSite.getDataSourceByModality("CDW") == null))
                {
                    result.fault = new FaultTO("Currently supporting Vista and CDW only");
                    return result;
                }

                // if we're asking for data from Vista - connect to sites
                if (selectedSite.getDataSourceByModality("HIS") != null)
                {
                    SiteArray sites = new AccountLib(mySession).patientVisit(pwd, site, pid, multiSite);
                    if (sites.fault != null)
                    {
                        result.fault = sites.fault;
                        return result;
                    }
                }
                else if (selectedSite.getDataSourceByModality("CDW") != null)
                {
                    mySession.ConnectionSet.Add(new CdwConnection(selectedSite.getDataSourceByModality("CDW")));
                }
                else
                {
                    // shoulnd't get here but leave as placeholder for future data sources
                }

                IndexedHashtable ihs = ClinicalApi.getPatientRecord(mySession.ConnectionSet, types);
                result = new PatientMedicalRecordTO(ihs);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            finally
            {
                try { mySession.ConnectionSet.disconnectAll(); }
                catch (Exception) { }
            }

            return result;
        }

        public BoolTO getIdProofingStatus(string patientId, string patientName, string patientDOB)
        {
            BoolTO result = new BoolTO();

            if (mySession == null || mySession.ConnectionSet == null || !mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(patientId))
            {
                result.fault = new FaultTO("Must supply patient ID");
            }

            if (result.fault != null)
            {
                return result;
            }

            Patient patient = new Patient()
            {
                SSN = new SocSecNum(patientId),
                Name = new PersonName(patientName),
                DOB = patientDOB
            };

            try
            {
                if (SocSecNum.isValid(patientId)) // if ID passed was SSN - turn in to 
                {
                    Patient[] matches = new PatientApi().match(mySession.ConnectionSet.BaseConnection, patient.SSN.toString());
                    if (matches == null || matches.Length == 0)
                    {
                        result.fault = new FaultTO("No patient found with that SSN!");
                        return result;
                    }
                    if (matches.Length > 0)
                    {
                        result.fault = new FaultTO("Duplicate SSN found", "Please contact the system administrator");
                        return result;
                    }
                    if (String.IsNullOrEmpty(matches[0].LocalPid))
                    {
                        result.fault = new FaultTO("Invalid record associated with that SSN", "Please contact the system administrator");
                        return result;
                    }
                    PatientTO vistaPatient = new PatientLib(mySession).select(matches[0].LocalPid);
                    patient.LocalPid = matches[0].LocalPid;
                    patient.MpiPid = vistaPatient.mpiPid;
                }

                //new AccountLib(mySession).setupMultiSourceQuery("pwd", patient.s 
                mySession.ConnectionSet.setLocalPids(patient.MpiPid);

                IndexedHashtable ihs = new PatientApi().getIdProofingStatus(mySession.ConnectionSet, patient);
                if (ihs == null || ihs.Count == 0)
                {
                    result.trueOrFalse = false;
                }
                else
                {
                    result.trueOrFalse = true;

                    for (int i = 0; i < ihs.Count; i++) // loop through - if any are false, result is false
                    {
                        if (!(bool)ihs.GetValue(i))
                        {
                            result.trueOrFalse = false;
                            break;
                        }
                    }
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            finally
            {
                try { mySession.ConnectionSet.disconnectAll(); }
                catch (Exception) { }
            }
            return result;
        }

        public TaggedTextArray updateIdProofingStatus(string patientId, string patientName, string patientDOB)
        {
            return new TaggedTextArray() { fault = new FaultTO(new NotImplementedException()) };
            TaggedTextArray result = new TaggedTextArray();
            try
            {
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            finally
            {
                try { mySession.ConnectionSet.disconnectAll(); }
                catch (Exception) { }
            }
            return result;
        }
    }
}
