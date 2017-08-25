using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao.oracle;
//using Oracle.DataAccess.Client;
//using Oracle.DataAccess.Types;
using System.Data.OracleClient;

namespace gov.va.medora.mdo.dao.oracle.vadir
{
    public class VadirPatientDao : IPatientDao
    {
        MdoOracleConnection _cxn;
        delegate OracleDataReader executeReader();

        public VadirPatientDao(AbstractConnection cxn)
        {
            _cxn = (MdoOracleConnection)cxn;
        }

        private bool isValidMosPatient(Patient p)
        {
            if (p == null)
            {
                return false;
            }

            decimal trash = 0;
            if (String.IsNullOrEmpty(p.EDIPI) || !Decimal.TryParse(p.EDIPI, out trash))
            {
                return false;
            }
            return true;
            // TBD - may allow name/ssn/dob lookup in the future. right now just supporting EDIPI
            //if ((p.Name != null && !String.IsNullOrEmpty(p.Name.Lastname)) && p.SSN != null && !String.IsNullOrEmpty(p.DOB))
            //{
            //    return true;
            //}
            //return false;
        }

        /// <summary>
        /// Fetch a VADIR formatted MOS report for a patient given the EDIPI or Name, SSN and DOB
        /// </summary>
        /// <param name="patient"></param>
        /// <returns>TextReport with report text set to VADIR report</returns>
        public TextReport getMOSReport(Patient patient)
        {
            if (!isValidMosPatient(patient))
            {
                throw new ArgumentException("Invalid patient. Need name, SSN and DOB or EDIPI");
            }

            //System.Data.OracleClient. OracleQuery query = new OracleQuery();
            
            System.Data.OracleClient.OracleCommand command = new System.Data.OracleClient.OracleCommand();
            command.CommandText = "BLUE_BUTTON.FETCHREPORT";
            command.CommandType = System.Data.CommandType.StoredProcedure;

            OracleParameter idParam = new System.Data.OracleClient.OracleParameter("VA_ID_IN", OracleType.Number);
            //OracleParameter idParam = new OracleParameter("VA_ID_IN", OracleDbType.Decimal);
            idParam.Direction = System.Data.ParameterDirection.Input;
            idParam.Value = Convert.ToDecimal(patient.EDIPI);
            command.Parameters.Add(idParam);

            OracleParameter lNameParam = new System.Data.OracleClient.OracleParameter("LNAME_IN", OracleType.VarChar, 26);
            //OracleParameter lNameParam = new OracleParameter("LNAME_IN", OracleDbType.Varchar2, 26);
            lNameParam.Direction = System.Data.ParameterDirection.Input;
            lNameParam.Value = "";
            command.Parameters.Add(lNameParam);

            OracleParameter ssnParam = new System.Data.OracleClient.OracleParameter("SSN_IN", OracleType.VarChar, 9);
            //OracleParameter ssnParam = new OracleParameter("SSN_IN", OracleDbType.Varchar2, 9);
            ssnParam.Direction = System.Data.ParameterDirection.Input;
            ssnParam.Value = "";
            command.Parameters.Add(ssnParam);

            OracleParameter dobParam = new System.Data.OracleClient.OracleParameter("DOB_IN", OracleType.DateTime);
            //OracleParameter dobParam = new OracleParameter("DOB_IN", OracleDbType.TimeStamp);
            dobParam.Direction = System.Data.ParameterDirection.Input;
            dobParam.Value = DBNull.Value;
            command.Parameters.Add(dobParam);

            OracleParameter returnParam = new OracleParameter("v_Return", OracleType.Clob);
            //OracleParameter returnParam = new OracleParameter("v_Return", OracleDbType.Clob);
            returnParam.Direction = System.Data.ParameterDirection.ReturnValue;
            command.Parameters.Add(returnParam);

            System.Data.OracleClient.OracleConnection cxn = new OracleConnection(_cxn.DataSource.ConnectionString);
            cxn.Open();

            try
            {
                command.Connection = cxn;
                OracleDataReader reader = command.ExecuteReader();
                //_cxn.connect();

                //executeReader executeReader = delegate() { return command.ExecuteReader(); };
                //OracleDataReader reader = (OracleDataReader)_cxn.query(query, executeReader);

                //if (query.Command.Parameters["v_Return"] == null || query.Command.Parameters["v_Return"].Value == DBNull.Value)
                //{
                //    return null;
                //}
                string text = ((System.Data.OracleClient.OracleLob)command.Parameters["v_Return"].Value).Value.ToString();
                return new TextReport() { Text = text };
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                cxn.Close();
            }
        }

        #region Not Implemented Members
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
