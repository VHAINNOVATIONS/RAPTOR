using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    public class FhiePatientDao : IPatientDao
    {
        VistaPatientDao vistaDao = null;
        AbstractConnection cxn;

        public FhiePatientDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
            vistaDao = new VistaPatientDao(cxn);
        }

        public Patient[] match(String target)
        {
            return vistaDao.match(target);
        }

        public Patient[] matchByNameCityState(string name, string city, string state)
        {
            return null;
        }

        public Patient[] matchByNameDOBGender(string name, string dob, string gender)
        {
            return null;
        }

        public Patient[] getPatientsByWard(string wardId)
        {
            return null;
        }

        public Patient[] getPatientsByClinic(string clinicId)
        {
            return null;
        }

        public Patient[] getPatientsByClinic(string clinicId, string fromDate, string toDate)
        {
            return null;
        }

        public Patient[] getPatientsBySpecialty(string specialtyId)
        {
            return null;
        }

        public Patient[] getPatientsByTeam(string teamId)
        {
            return null;
        }

        public Patient[] getPatientsByProvider(string duz)
        {
            return null;
        }

        public Patient select()
        {
            if (StringUtils.isEmpty(cxn.Pid))
            {
                throw new Exception("Local PID has not been set");
            }
            return select(cxn.Pid);
        }

        public Patient select(string dfn)
        {
            Patient result = vistaDao.selectByRpc(dfn);
            result.SiteIDs = vistaDao.getSiteIDs(result.LocalPid);
            return result;
        }

        public Patient selectBySSN(string ssn)
        {
            return vistaDao.selectBySSN(ssn);
        }

        public String getLocalPid(String mpiPID)
        {
            return vistaDao.getLocalPid(mpiPID);
        }

        public bool isTestPatient()
        {
            return false;
        }

        public KeyValuePair<int, string> getConfidentiality()
        {
            return new KeyValuePair<int, string>(0, "");
        }

        public string issueConfidentialityBulletin() 
        {
            return "OK";
        }

        public StringDictionary getRemoteSiteIds(String pid)
        {
            return vistaDao.getRemoteSiteIds(pid);
        }

        public Site[] getRemoteSites(String pid)
        {
            return vistaDao.getRemoteSites(pid);
        }

        public User[] getInpatientProviders(String pid)
        {
            return null;
        }

        public User[] getAllProviders(String pid)
        {
            return null;
        }

        public OEF_OIF[] getOefOif()
        {
            return null;
        }

        public void addHomeData(Patient patient) { }

        public PatientAssociate[] getPatientAssociates(string pid)
        {
            return null;
        }

        public StringDictionary getPatientTypes()
        {
            return null;
        }

        public string patientInquiry(string pid)
        {
            return null;
        }

        public RatedDisability[] getRatedDisabilities()
        {
            return null;
        }

        public RatedDisability[] getRatedDisabilities(string dfn)
        {
            return null;
        }

        public KeyValuePair<string, string> getPcpForPatient(string dfn)
        {
            return new KeyValuePair<string, string>(null, null);
        }


        public TextReport getMOSReport(Patient patient)
        {
            throw new NotImplementedException();
        }

        public Dictionary<string, string> getTreatingFacilityIds(string pid)
        {
            throw new NotImplementedException();
        }




        public DemographicSet getDemographics()
        {
            throw new NotImplementedException();
        }


        public bool isIdentityProofed(Patient patient)
        {
            throw new NotImplementedException();
        }
    }
}
