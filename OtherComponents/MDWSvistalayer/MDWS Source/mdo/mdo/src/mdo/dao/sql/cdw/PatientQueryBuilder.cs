using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class PatientQueryBuilder
    {
        public SqlDataAdapter buildPatientSelectQuery(string patientIcn, string queryString)
        {
            SqlParameter patientIdParameter = new SqlParameter("@patientIcn", System.Data.SqlDbType.VarChar, 50);
            patientIdParameter.Value = patientIcn;

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);
            adapter.SelectCommand.Parameters.Add(patientIdParameter);

            return adapter;
        }

        public void addAdditionalVarCharParameters(Dictionary<String, String> parameters, SqlDataAdapter adapter)
        {
            if (parameters == null) return;

            foreach(KeyValuePair<String, String> kv in parameters) {
                SqlParameter queryParam = new SqlParameter(kv.Key, System.Data.SqlDbType.VarChar, 50);
                queryParam.Value = kv.Value;
                adapter.SelectCommand.Parameters.Add(queryParam);
            }
        }
    }
}
