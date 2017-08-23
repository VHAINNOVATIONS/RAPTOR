using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.sm.admin;
using gov.va.medora.mdo.sm.query;
using gov.va.medora.mdo.sm.tiu;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.soap.sm
{
    public class SmPatientDao : IPatientDao
    {
        QueryService _svc = new QueryService();
        private AbstractConnection _cxn;

        public SmPatientDao(AbstractConnection cxn)
        {
            if (cxn == null || cxn.DataSource == null || String.IsNullOrEmpty(cxn.DataSource.ConnectionString))
            {
                throw new MdoException(MdoExceptionCode.DATA_SOURCE_MISSING_CXN_STRING, "Must supply the SM service endpoint");
            }
            _cxn = cxn;
            _svc.Url = cxn.DataSource.ConnectionString;
        }


        public DemographicSet getDemographics(string sitecode, string patientId)
        {
            PatientDemographicsResponse response = _svc.getPatientDemographics(new mdo.sm.query.Patient() { ICN = patientId }, sitecode);

            if (response == null || response.Patient == null || 
                String.Equals(response.Status, "reject", StringComparison.CurrentCultureIgnoreCase) || 
                String.Equals(response.Status, "error", StringComparison.CurrentCultureIgnoreCase))
            {
                throw new MdoException(MdoExceptionCode.DATA_MISSING_REQUIRED, response.Reason);
            }

            DemographicSet demogs = new DemographicSet();
            demogs.EmailAddresses = new List<EmailAddress>();
            demogs.EmailAddresses.Add(new EmailAddress(response.Patient.EmailAddress));
            demogs.Names = new List<PersonName>();
            demogs.Names.Add(
                new PersonName()
                {
                    Firstname = response.Patient.FirstName,
                    Lastname = response.Patient.LastName,
                });
            demogs.PhoneNumbers = new List<PhoneNum>();
            demogs.PhoneNumbers.Add(new PhoneNum(response.Patient.HomePhone));
            if (response.Patient.HomeAddress != null && response.Patient.HomeAddress.Address != null)
            {
                demogs.StreetAddresses = new List<Address>();
                demogs.StreetAddresses.Add(
                    new Address()
                    {
                        Street1 = response.Patient.HomeAddress.Address.AddressLine1,
                        Street2 = response.Patient.HomeAddress.Address.AddressLine2,
                        City = response.Patient.HomeAddress.Address.City,
                        County = response.Patient.HomeAddress.Address.County,
                        State = response.Patient.HomeAddress.Address.State,
                        Zipcode = response.Patient.HomeAddress.Address.ZipCode
                    });
            }

            return demogs;
        }


        #region Not Implemented
        public Dictionary<string, string> getTreatingFacilityIds(string pid)
        {
            throw new NotImplementedException();
        }

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

        public Patient select(string pid)
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
        #endregion



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
