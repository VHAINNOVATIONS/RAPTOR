using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Oracle.DataAccess.Client;
using System.Data;

namespace gov.va.medora.mdo.dao.oracle.mhv.evault
{
    public class UserProfileDao
    {
        MdoOracleConnection _cxn;
        delegate OracleDataReader reader();

        public UserProfileDao(AbstractConnection cxn)
        {
            _cxn = (MdoOracleConnection)cxn;
        }

        public bool isValidUser(String icn)
        {
            OracleQuery query = buildIsValidUserQuery(icn);
            reader selectQuery = delegate() { return query.Command.ExecuteReader(); };
            return toIsValidUser((OracleDataReader)_cxn.query(query, selectQuery));
        }

        internal OracleQuery buildIsValidUserQuery(String icn)
        {
            //String sql = "SELECT USER_PROFILE_ID FROM EVAULT.USER_PROFILE WHERE IS_PATIENT=1 AND IS_VETERAN=1 AND USER_PROFILE_DEACT_REASON_ID IS NULL AND " +
            //    "ACCEPT_TERMS=1 AND ACCEPT_SM_TERMS=1 AND USER_NAME=:smUserName";
            String sql = "SELECT DISTINCT USER_PROFILE_ID, PAT.ICN FROM EVAULT.USER_PROFILE PROF JOIN EVAULT.PATIENT PAT ON PAT.USER_PROFILE_USER_PROFILE_ID=PROF.USER_PROFILE_ID " +
                "JOIN EVAULT.IPA IPA ON IPA.PATIENT_PATIENT_ID=PAT.PATIENT_ID WHERE PROF.IS_PATIENT=1 AND IPA.STATUS='Authenticated' AND PROF.IS_VETERAN=1 " +
                "AND PROF.USER_PROFILE_DEACT_REASON_ID IS NULL AND PROF.ACCEPT_TERMS=1 AND PROF.ACCEPT_SM_TERMS=1 AND PAT.ICN = :icn";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter icnParam = new OracleParameter("icn", OracleDbType.Char, 17);
            icnParam.Value = icn;
            query.Command.Parameters.Add(icnParam);

            return query;
        }

        internal bool toIsValidUser(IDataReader rdr)
        {
            if (rdr != null && rdr.Read())
            {
                return true;
            }
            return false;
        }
    }
}
