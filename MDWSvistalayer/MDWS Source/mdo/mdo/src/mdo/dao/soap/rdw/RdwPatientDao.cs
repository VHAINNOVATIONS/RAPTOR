using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.soap.rdw
{
    public class RdwPatientDao : IPatientDao
    {
        RdwConnection _cxn;

        public RdwPatientDao(AbstractConnection cxn)
        {
            _cxn = (RdwConnection)cxn;
        }

        public Patient select(string pid)
        {
            _cxn.Pid = pid;
            return new Patient() { LocalPid = pid };
        }


        #region Not Implemented Members
        public Patient[] match(string target)
        {
            throw new NotImplementedException();
        }

        public Patient[] getPatientsByWard(string wardId)
        {
            throw new NotImplementedException();
        }

        public Patient[] getPatientsByClinic(string clinicId)
        {
            throw new NotImplementedException();
        }

        public Patient[] getPatientsByClinic(string clinicId, string fromDate, string toDate)
        {
            throw new NotImplementedException();
        }

        public Patient[] getPatientsBySpecialty(string specialtyId)
        {
            throw new NotImplementedException();
        }

        public Patient[] getPatientsByTeam(string teamId)
        {
            throw new NotImplementedException();
        }

        public Patient[] getPatientsByProvider(string providerId)
        {
            throw new NotImplementedException();
        }

        public Patient[] matchByNameCityState(string name, string city, string stateAbbr)
        {
            throw new NotImplementedException();
        }

        public Patient select()
        {
            throw new NotImplementedException();
        }

        public Patient selectBySSN(string ssn)
        {
            throw new NotImplementedException();
        }

        public string getLocalPid(string mpiPID)
        {
            throw new NotImplementedException();
        }

        public bool isTestPatient()
        {
            throw new NotImplementedException();
        }

        public KeyValuePair<int, string> getConfidentiality()
        {
            throw new NotImplementedException();
        }

        public string issueConfidentialityBulletin()
        {
            throw new NotImplementedException();
        }

        public System.Collections.Specialized.StringDictionary getRemoteSiteIds(string pid)
        {
            throw new NotImplementedException();
        }

        public Site[] getRemoteSites(string pid)
        {
            throw new NotImplementedException();
        }

        public OEF_OIF[] getOefOif()
        {
            throw new NotImplementedException();
        }

        public void addHomeData(Patient patient)
        {
            throw new NotImplementedException();
        }

        public PatientAssociate[] getPatientAssociates(string pid)
        {
            throw new NotImplementedException();
        }

        public System.Collections.Specialized.StringDictionary getPatientTypes()
        {
            throw new NotImplementedException();
        }

        public string patientInquiry(string pid)
        {
            throw new NotImplementedException();
        }

        public RatedDisability[] getRatedDisabilities()
        {
            throw new NotImplementedException();
        }

        public RatedDisability[] getRatedDisabilities(string pid)
        {
            throw new NotImplementedException();
        }

        public KeyValuePair<string, string> getPcpForPatient(string dfn)
        {
            throw new NotImplementedException();
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
        #endregion


        public bool isIdentityProofed(Patient patient)
        {
            throw new NotImplementedException();
        }
    }
}
