using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Oracle.DataAccess.Client;

namespace gov.va.medora.mdo.dao.oracle.mhv.sm
{
    public class SystemDao
    {
        MdoOracleConnection _cxn;
        delegate object scalar();

        public SystemDao(AbstractConnection cxn)
        {
            _cxn = (MdoOracleConnection)cxn;
        }

        public DateTime getSystemTime()
        {
            OracleQuery request = buildGetSystemTimeQuery();
            scalar resultDelegate = delegate() { return request.Command.ExecuteScalar(); };
            return (DateTime)_cxn.query(request, resultDelegate);
        }

        internal OracleQuery buildGetSystemTimeQuery()
        {
            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand("SELECT SYSTIMESTAMP FROM DUAL");
            return query;
        }
    }
}
