using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.sql.npt;

namespace gov.va.medora.mdo.api
{
    public class PatientApi
    {
	    string DAO_NAME = "IPatientDao";
    	
	    public PatientApi() {}

        public Patient[] match(AbstractConnection cxn, string target)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).match(target);
        }

        public IndexedHashtable match(ConnectionSet cxns, string target)
        {
            return cxns.query(DAO_NAME, "match", new object[] { target });
        }

        public Patient[] matchByNameCityState(AbstractConnection cxn, string name, string city, string state)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).matchByNameCityState(name,city,state);
        }

        public IndexedHashtable matchByNameCityState(ConnectionSet cxns, string name, string city, string state)
        {
            return cxns.query(DAO_NAME, "matchByNameCityState", new object[] { name,city,state });
        }

        public Patient[] getPatientsByWard(AbstractConnection cxn, string wardId)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getPatientsByWard(wardId);
        }

        public Patient[] getPatientsByClinic(AbstractConnection cxn, string clinicId)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getPatientsByClinic(clinicId);
        }

        public Patient[] getPatientsByClinic(AbstractConnection cxn, string clinicId, string fromDate, string toDate)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getPatientsByClinic(clinicId, fromDate, toDate);
        }

        public Patient[] getPatientsBySpecialty(AbstractConnection cxn, string specialtyId)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getPatientsBySpecialty(specialtyId);
        }

        public Patient[] getPatientsByTeam(AbstractConnection cxn, string teamId)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getPatientsByTeam(teamId);
        }

        public Patient[] getPatientsByProvider(AbstractConnection cxn, string duz)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getPatientsByProvider(duz);
        }

        public Patient select(AbstractConnection cxn, string pid)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).select(pid);
        }

        public IndexedHashtable select(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "select", new object[] { });
        }

	    public string getLocalPid(AbstractConnection cxn, string mpiPID)
	    {
		    return ((IPatientDao)cxn.getDao(DAO_NAME)).getLocalPid(mpiPID);
	    }

        public IndexedHashtable getLocalPids(ConnectionSet cxns, string mpiPID)
        {
            return cxns.query(DAO_NAME, "getLocalPid", new Object[] { mpiPID });
        }

        public IndexedHashtable setLocalPids(ConnectionSet cxns, string mpiPid)
        {
            IndexedHashtable result = cxns.query(DAO_NAME, "getLocalPid", new object[] { mpiPid });
            for (int i = 0; i < result.Count; i++)
            {
                if (result.GetValue(i).GetType().Name.EndsWith("Exception"))
                {
                    continue;
                }
                string siteId = (string)result.GetKey(i);
                AbstractConnection cxn = cxns.getConnection(siteId);
                cxn.Pid = (string)result.GetValue(i);
            }
            return result;
        }

        public bool isTestPatient(AbstractConnection cxn)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).isTestPatient();
        }

        public IndexedHashtable isTestPatient(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "isTestPatient", new object[] { });
        }

        public KeyValuePair<int, string> getConfidentiality(AbstractConnection cxn)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getConfidentiality();
        }

        public IndexedHashtable getConfidentiality(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getConfidentiality", new object[] { });
        }

        public StringDictionary getRemoteSiteIds(AbstractConnection cxn, string pid)
	    {
		    return ((IPatientDao)cxn.getDao(DAO_NAME)).getRemoteSiteIds(pid);
	    }

        public string issueConfidentialityBulletin(AbstractConnection cxn)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).issueConfidentialityBulletin();
        }

        public IndexedHashtable issueConfidentialityBulletin(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "issueConfidentialityBulletin", new object[] { });
        }

        public Site[] getRemoteSites(AbstractConnection cxn, string pid)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getRemoteSites(pid);
        }

        public IndexedHashtable getPatientSelectionData(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getPatientSelectionData", new Object[] { });
        }

        public OEF_OIF[] getOefOif(AbstractConnection cxn)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getOefOif();
        }

        public IndexedHashtable getOefOif(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getOefOif", new object[] { });
        }

        public void addHomeDate(AbstractConnection cxn, Patient patient)
        {
            ((IPatientDao)cxn.getDao(DAO_NAME)).addHomeData(patient);
        }

        public Patient[] mpiMatch(DataSource src, string ssn)
        {
            gov.va.medora.mdo.dao.hl7.mpi.MpiConnection cxn = new gov.va.medora.mdo.dao.hl7.mpi.MpiConnection(src);
            gov.va.medora.mdo.dao.hl7.mpi.MpiPatientDao dao = new gov.va.medora.mdo.dao.hl7.mpi.MpiPatientDao(cxn);
            return dao.match(ssn);
        }

        public PatientAssociate[] getPatientAssociates(AbstractConnection cxn, string pid)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getPatientAssociates(pid);
        }

        public IndexedHashtable getPatientAssociates(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getPatientAssociates", new Object[] { });
        }

        public string patientInquiry(AbstractConnection cxn, string pid)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).patientInquiry(pid);
        }

        public StringDictionary getPatientTypes(AbstractConnection cxn)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getPatientTypes();
        }

        public IndexedHashtable getPatientTypes(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getPatientTypes", new Object[] { });
        }

        public IndexedHashtable patientInquiry(ConnectionSet cxns, string pid)
        {
            return cxns.query(DAO_NAME, "patientInquiry", new Object[] { pid });
        }

        public Patient[] nptMatch(string ssn)
        {
            NptPatientDao dao = new NptPatientDao(new NptConnection(new DataSource()));
            return dao.getPatient(ssn);
        }

        /// <summary>
        /// Match on Patient.Name, Patient.DOB, and Patient.SSN
        /// </summary>
        /// <param name="patient">The patient to match</param>
        /// <param name="connectionString">The SQL connection string</param>
        /// <returns>An array of matching patients</returns>
        public Patient[] nptLookup(Patient patient)
        {
            NptPatientDao dao = new NptPatientDao(new NptConnection(new DataSource()));
            return dao.getPatient(patient);
        }

        public RatedDisability[] getRatedDisabiliities(AbstractConnection cxn)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getRatedDisabilities();
        }

        public RatedDisability[] getRatedDisabiliities(AbstractConnection cxn, string pid)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getRatedDisabilities(pid);
        }

        public IndexedHashtable getRatedDisabiliities(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getRatedDisabiliities", new Object[] { });
        }

        public TextReport getMOSReport(AbstractConnection cxn, Patient patient)
        {
            gov.va.medora.mdo.dao.oracle.vadir.VadirPatientDao vadirPatientDao = new dao.oracle.vadir.VadirPatientDao(cxn);
            return vadirPatientDao.getMOSReport(patient);
        }

        /// <summary>
        /// Get patient identifiers from the treating facility file at a single Vista (typically the base connection)
        /// </summary>
        /// <param name="cxn">Typically a VistaConnection</param>
        /// <param name="pid">Patient ID - typically a DFN</param>
        /// <returns>Dictionary of patient identifiers where key is the site ID and value is the patient ID at that site</returns>
        public Dictionary<string, string> getTreatingFacilityIds(AbstractConnection cxn, string pid)
        {
            return ((IPatientDao)cxn.getDao(DAO_NAME)).getTreatingFacilityIds(pid);
        }

        public IndexedHashtable getIdProofingStatus(ConnectionSet cxns, Patient patient)
        {
            return cxns.query(DAO_NAME, "isIdentityProofed", new object[] { patient });
        }
    }
}
