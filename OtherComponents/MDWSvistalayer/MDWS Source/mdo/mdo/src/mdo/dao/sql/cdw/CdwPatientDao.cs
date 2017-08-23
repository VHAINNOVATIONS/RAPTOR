using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;
using gov.va.medora.mdo.exceptions;
using System.Data;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwPatientDao : IPatientDao
    {
        CdwConnection _cxn;

        public CdwPatientDao(AbstractConnection cxn)
        {
            _cxn = cxn as CdwConnection;
        }

        public Patient[] match(string target)
        {
            if (!SocSecNum.isValid(target))
            {
                throw new NotImplementedException("non-SSN matches are currently not supported by CDW");
            }

            SqlCommand cmd = new SqlCommand("SELECT * FROM SPatient.SPatient WHERE PatientSSN=@target;");
            SqlParameter targetParam = new SqlParameter("@target", System.Data.SqlDbType.VarChar, 9);
            targetParam.Value = target;

            SqlDataAdapter adapter = new SqlDataAdapter(cmd);
            adapter.SelectCommand = new SqlCommand(cmd.CommandText);
            adapter.SelectCommand.Parameters.Add(targetParam);
            
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            IDictionary<string, Patient> patients = new Dictionary<string, Patient>();

            //if (!reader..HasRows)
            //{
            //    return new Patient[0];
            //}
            while (reader.Read())
            {
                Patient p = new Patient();
                p.LocalSiteId = (reader.GetInt16(reader.GetOrdinal("Sta3n"))).ToString();
                p.LocalPid = reader.GetString(reader.GetOrdinal("PatientIEN"));

                p.Name = new PersonName(reader.GetString(reader.GetOrdinal("PatientName")));
                if (!reader.IsDBNull(reader.GetOrdinal("PatientSSN")))
                {
                    p.SSN = new SocSecNum(reader.GetString(reader.GetOrdinal("PatientSSN")));
                }

                if (!reader.IsDBNull(reader.GetOrdinal("Gender")))
                {
                    p.Gender = reader.GetString(reader.GetOrdinal("Gender"));
                }
                else
                {
                    p.Gender = "";
                }
                if (!reader.IsDBNull(reader.GetOrdinal("DateOfBirthText")))
                {
                    p.DOB = reader.GetString(reader.GetOrdinal("DateOfBirthText"));
                }
                if (!reader.IsDBNull(reader.GetOrdinal("PatientICN")))
                {
                    p.MpiPid = (reader.GetString(reader.GetOrdinal("PatientICN"))).ToString();
                }
                else
                {
                    // use SSN for patient ICN
                    if (p.SSN == null || String.IsNullOrEmpty(p.SSN.toString()))
                    {
                        throw new MdoException(MdoExceptionCode.DATA_MISSING_REQUIRED, "Unable to process results for " + target + " - CDW record contains no ICN and no SSN");
                    }
                    p.MpiPid = p.SSN.toString();
                }

                p.Demographics = new Dictionary<string, DemographicSet>();
                DemographicSet demogs = new DemographicSet();
                demogs.PhoneNumbers = new List<PhoneNum>();
                demogs.EmailAddresses = new List<EmailAddress>();
                demogs.StreetAddresses = new List<Address>();

                if (!reader.IsDBNull(reader.GetOrdinal("PhoneResidence")))
                {
                    demogs.PhoneNumbers.Add(new PhoneNum(reader.GetString(reader.GetOrdinal("PhoneResidence"))));
                }
                if (!reader.IsDBNull(reader.GetOrdinal("PhoneWork")))
                {
                    demogs.PhoneNumbers.Add(new PhoneNum(reader.GetString(reader.GetOrdinal("PhoneWork"))));
                }

                Address address = new Address();
                if (!reader.IsDBNull(reader.GetOrdinal("StreetAddress1")))
                {
                    address.Street1 = reader.GetString(reader.GetOrdinal("StreetAddress1"));
                }
                if (!reader.IsDBNull(reader.GetOrdinal("StreetAddress2")))
                {
                    address.Street2 = reader.GetString(reader.GetOrdinal("StreetAddress2"));
                }
                if (!reader.IsDBNull(reader.GetOrdinal("StreetAddress3")))
                {
                    address.Street3 = reader.GetString(reader.GetOrdinal("StreetAddress3"));
                }
                if (!reader.IsDBNull(reader.GetOrdinal("City")))
                {
                    address.City = reader.GetString(reader.GetOrdinal("City"));
                }
                if (!reader.IsDBNull(reader.GetOrdinal("county")))
                {
                    address.County = reader.GetString(reader.GetOrdinal("county"));
                }
                if (!reader.IsDBNull(reader.GetOrdinal("State")))
                {
                    address.State = reader.GetString(reader.GetOrdinal("State"));
                }
                if (!reader.IsDBNull(reader.GetOrdinal("Zip")))
                {
                    address.Zipcode = reader.GetString(reader.GetOrdinal("Zip"));
                }
                demogs.StreetAddresses.Add(address);

                p.Demographics.Add(p.LocalSiteId, demogs);

                if (!patients.ContainsKey(p.MpiPid))
                {
                    p.SitePids = new System.Collections.Specialized.StringDictionary();
                    p.SitePids.Add(p.LocalSiteId, p.LocalPid);
                    patients.Add(p.MpiPid, p);
                }
                else
                {
                    if (!(patients[p.MpiPid].SitePids.ContainsKey(p.LocalSiteId)))
                    {
                        patients[p.MpiPid].SitePids.Add(p.LocalSiteId, p.LocalPid);
                    }

                    patients[p.MpiPid].Demographics.Add(p.LocalSiteId, p.Demographics[p.LocalSiteId]);
                }
            }

            // cleanup - need to set all temp ICNs back to null
            foreach (string key in patients.Keys)
            {
                if (!(patients[key].SSN == null) && !String.IsNullOrEmpty(patients[key].SSN.toString()) &&
                    !String.IsNullOrEmpty(patients[key].MpiPid) && String.Equals(patients[key].MpiPid, patients[key].SSN.toString()))
                {
                    patients[key].MpiPid = null;
                }
            }

            Patient[] result = new Patient[patients.Count];
            patients.Values.CopyTo(result, 0);
            return result;
        }

        public Dictionary<string, string> getTreatingFacilityIds(string pid)
        {
            SqlDataAdapter request = buildGetTreatingFacilityIdsRequest(pid);
            IDataReader rdr = (IDataReader)_cxn.query(request);
            return toPatientIds(rdr);
        }

        internal SqlDataAdapter buildGetTreatingFacilityIdsRequest(string icn)
        {
            string sql = "SELECT Sta3n, PatientIEN FROM SPatient.SPatient WHERE PatientICN=@icn;";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(sql);

            SqlParameter patientIdParam = new SqlParameter("@icn", System.Data.SqlDbType.VarChar, 50);
            patientIdParam.Value = icn;
            adapter.SelectCommand.Parameters.Add(patientIdParam);

            adapter.SelectCommand.CommandTimeout = 600; // allow query to run for up to 10 minutes
            return adapter;
        }

        internal Dictionary<string, string> toPatientIds(IDataReader rdr)
        {
            Dictionary<string, string> ids = new Dictionary<string, string>();

            while (rdr.Read())
            {
                ids.Add(Convert.ToString(rdr.GetInt16(0)), rdr.GetString(1));
            }

            return ids;
        }

        #region Not implemented members
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
            _cxn.Pid = pid;
            return new Patient() { LocalPid = pid };
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
        #endregion



        public TextReport getMOSReport(Patient patient)
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
