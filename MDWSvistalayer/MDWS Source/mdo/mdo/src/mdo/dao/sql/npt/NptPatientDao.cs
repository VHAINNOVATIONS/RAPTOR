using System;
using System.Collections.Generic;
using System.Text;
using System.Data;
using System.Data.SqlClient;
using gov.va.medora.mdo;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.sql.npt
{
    public class NptPatientDao : IPatientDao
    {
        const string PATIENT_TABLE = "Patient";
        NptConnection _myCxn;

        public NptPatientDao(AbstractConnection cxn)
        {
            _myCxn = (NptConnection)cxn;
        }

        public Dictionary<string, string> getTreatingFacilityIds(string pid)
        {
            SqlDataAdapter request = buildGetTreatingFacilityRequest(pid);
            IDataReader response = (SqlDataReader)_myCxn.query(request);
            return toTreatingFacilityIdsFromReader(response);
        }

        internal SqlDataAdapter buildGetTreatingFacilityRequest(string pid)
        {
            SqlCommand cmd = new SqlCommand("SELECT * FROM " + PATIENT_TABLE + " WHERE icn=@icn;");
            SqlDataAdapter adapter = new SqlDataAdapter(cmd);
            adapter.SelectCommand = new SqlCommand();

            SqlParameter icnParam = new SqlParameter("@icn", System.Data.SqlDbType.Char, 9);
            icnParam.Value = pid;
            adapter.SelectCommand.Parameters.Add(icnParam);

            return adapter;
        }

        internal Dictionary<string, string> toTreatingFacilityIdsFromReader(IDataReader rdr)
        {
            throw new NotImplementedException();
        }

        /// <summary>
        /// Match a patient by SSN
        /// </summary>
        /// <param name="target">Social Security Number (SSN)</param>
        /// <returns>Arrary of Patient with target SSN</returns>
        public Patient[] match(string target)
        {
            return getPatient(target);
        }

        #region Not Implemented Members
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

        public User[] getInpatientProviders(string pid)
        {
            throw new NotImplementedException();
        }

        public User[] getAllProviders(string pid)
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
        #endregion

        /// <summary>
        /// Match a patient on Name, SSN and DOB
        /// </summary>
        /// <param name="p">The patient to match</param>
        /// <returns>An array of patients matching the passed patient</returns>
        public Patient[] getPatient(Patient p)
        {
            if (p == null || p.SSN == null || !p.SSN.IsValid || p.Name == null || String.IsNullOrEmpty(p.DOB))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Must supply valid SSN, Name and DOB to match");
            }
            try
            {
                utils.DateUtils.IsoDateStringToDateTime(p.DOB);
            }
            catch (Exception)
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Patient DOB should be in ISO format: YYYYMMDD");
            }

            //SqlTransaction tx = conn.BeginTransaction();
            SqlCommand cmd = new SqlCommand("SELECT * FROM " + PATIENT_TABLE + 
                " WHERE ssn=@SSN AND name=@NAME AND vistaDOB=@DOB;");

            SqlParameter ssnParam = new SqlParameter("@SSN", System.Data.SqlDbType.Char, 9);
            ssnParam.Value = p.SSN.toString();
            cmd.Parameters.Add(ssnParam);

            SqlParameter nameParam = new SqlParameter("@NAME", SqlDbType.VarChar, 255);
            nameParam.Value = p.Name.LastNameFirst;
            cmd.Parameters.Add(nameParam);

            SqlParameter dobParam = new SqlParameter("@DOB", SqlDbType.VarChar, 7);
            dobParam.Value = vista.VistaTimestamp.fromDateTimeShortString(utils.DateUtils.IsoDateStringToDateTime(p.DOB));
            cmd.Parameters.Add(dobParam);

            _myCxn.SqlParameters = cmd.Parameters; // kind of a hack to use SQL parameters
            _myCxn.connect();
            SqlDataReader reader = (SqlDataReader)_myCxn.query(cmd.CommandText);

            try
            {
                IList<Patient> list = getPatientsFromDataReader(reader);
                Patient[] patients = new Patient[list.Count];
                list.CopyTo(patients, 0);
                return patients;
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                if (reader != null)
                {
                    reader.Close();
                }
                _myCxn.disconnect();
            }
        }


        /// <summary>
        /// Query NPT database for all patients with a given SSN
        /// </summary>
        /// <param name="ssn">SSN (Social Security Number)</param>
        /// <returns>An array of all patients matching the given SSN or an empty collection
        /// if not matches are located</returns>
        public Patient[] getPatient(string ssn)
        {
            if (String.IsNullOrEmpty(ssn))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL_SSN);
            }

            SqlCommand cmd = new SqlCommand("SELECT * FROM " + PATIENT_TABLE + " WHERE ssn=@SSN;");

            SqlParameter ssnParam = new SqlParameter("@SSN", System.Data.SqlDbType.Char, 9);
            ssnParam.Value = ssn;
            cmd.Parameters.Add(ssnParam);

            _myCxn.SqlParameters = cmd.Parameters; // kind of a hack to use SQL parameters
            _myCxn.connect();
            SqlDataReader reader = (SqlDataReader)_myCxn.query(cmd.CommandText);

            try
            {
                IList<Patient> list = getPatientsFromDataReader(reader);
                Patient[] p = new Patient[list.Count];
                list.CopyTo(p, 0);
                return p;
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                if (reader != null)
                {
                    reader.Close();
                }
                _myCxn.disconnect();
            }
        }

        IList<Patient> getPatientsFromDataReader(SqlDataReader reader)
        {
            IList<Patient> patients = new List<Patient>();
            IDictionary<string, Patient> patientDictionary = new Dictionary<string, Patient>();

            while (reader.Read())
            {
                patients.Add(getNextPatient(reader));
            }

            foreach (Patient p in patients)
            {
                if (!patientDictionary.ContainsKey(p.MpiPid))
                {
                    patientDictionary.Add(p.MpiPid, p);
                    p.SitePids = new System.Collections.Specialized.StringDictionary();
                    p.SitePids.Add(p.LocalSiteId, p.LocalPid);
                }
                else
                {
                    if (!(patientDictionary[p.MpiPid].SitePids.ContainsKey(p.LocalSiteId)))
                    {
                        patientDictionary[p.MpiPid].SitePids.Add(p.LocalSiteId, p.LocalPid);
                    }

                    patientDictionary[p.MpiPid].Demographics.Add(p.LocalSiteId, p.Demographics[p.LocalSiteId]);
                }
            }

            patients.Clear();
            foreach (Patient p in patientDictionary.Values)
            {
                patients.Add(p);
            }
            return patients;
        }

        Patient getNextPatient(SqlDataReader reader)
        {
            Patient p = new Patient();
            p.LocalSiteId = (reader.GetInt16(reader.GetOrdinal("sitecode"))).ToString();

            Decimal d = Convert.ToDecimal(reader.GetDecimal(reader.GetOrdinal("localPid")));
            if ((d - Math.Floor(d)) > 0)
            {
                p.LocalPid = (d).ToString();
            }
            else
            {
                p.LocalPid = (Convert.ToInt64(d)).ToString();
            }
            p.Name = new PersonName(reader.GetString(reader.GetOrdinal("name")));
            p.SSN = new SocSecNum(reader.GetString(reader.GetOrdinal("ssn")));

            if (!reader.IsDBNull(reader.GetOrdinal("gender")))
            {
                p.Gender = reader.GetBoolean(reader.GetOrdinal("gender")) ? "M" : "F"; // 1 or true == male, 0 or false == female 
            }
            else
            {
                p.Gender = "";
            }
            if (!reader.IsDBNull(reader.GetOrdinal("vistaDOB")))
            {
                p.DOB = reader.GetString(reader.GetOrdinal("vistaDOB"));
            }
            p.MpiPid = (reader.GetInt32(reader.GetOrdinal("icn"))).ToString();
            p.MpiChecksum = (reader.GetInt32(reader.GetOrdinal("icnx"))).ToString();

            p.Demographics = new Dictionary<string, DemographicSet>();
            DemographicSet demogs = new DemographicSet();
            demogs.PhoneNumbers = new List<PhoneNum>();
            demogs.EmailAddresses = new List<EmailAddress>();
            demogs.StreetAddresses = new List<Address>();
            
            if (!reader.IsDBNull(reader.GetOrdinal("homePhone")))
            {
                demogs.PhoneNumbers.Add(new PhoneNum(reader.GetString(reader.GetOrdinal("homePhone"))));
            }
            if (!reader.IsDBNull(reader.GetOrdinal("workPhone")))
            {
                demogs.PhoneNumbers.Add(new PhoneNum(reader.GetString(reader.GetOrdinal("workPhone"))));
            }
            if (!reader.IsDBNull(reader.GetOrdinal("cellPhone")))
            {
                demogs.PhoneNumbers.Add(new PhoneNum(reader.GetString(reader.GetOrdinal("cellPhone"))));
            }

            if (!reader.IsDBNull(reader.GetOrdinal("emailAddress")))
            {
                demogs.EmailAddresses.Add(new EmailAddress(reader.GetString(reader.GetOrdinal("emailAddress"))));
            }

            Address address = new Address();
            if (!reader.IsDBNull(reader.GetOrdinal("addressLine1")))
            {
                address.Street1 = reader.GetString(reader.GetOrdinal("addressLine1"));
            }
            if (!reader.IsDBNull(reader.GetOrdinal("addressLine2")))
            {
                address.Street2 = reader.GetString(reader.GetOrdinal("addressLine2"));
            }
            if (!reader.IsDBNull(reader.GetOrdinal("addressLine3")))
            {
                address.Street3 = reader.GetString(reader.GetOrdinal("addressLine3"));
            }
            if (!reader.IsDBNull(reader.GetOrdinal("city")))
            {
                address.City = reader.GetString(reader.GetOrdinal("city"));
            }
            if (!reader.IsDBNull(reader.GetOrdinal("county")))
            {
                address.County = reader.GetString(reader.GetOrdinal("county"));
            }
            if (!reader.IsDBNull(reader.GetOrdinal("state")))
            {
                address.State = reader.GetString(reader.GetOrdinal("state"));
            }
            if (!reader.IsDBNull(reader.GetOrdinal("zipcode")))
            {
                address.Zipcode = reader.GetString(reader.GetOrdinal("zipcode"));
            }
            demogs.StreetAddresses.Add(address);

            p.Demographics.Add(p.LocalSiteId, demogs);

            return p;
        }

        public KeyValuePair<string, string> getPcpForPatient(string dfn)
        {
            return new KeyValuePair<string, string>(null, null);
        }


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
