using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Oracle.DataAccess.Client;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.domain.sm;
using gov.va.medora.mdo.domain.sm.enums;
using System.Data;

namespace gov.va.medora.mdo.dao.oracle.mhv.sm
{
    public class MessageActivityDao
    {
        MdoOracleConnection _cxn;
        delegate OracleDataReader reader();
        delegate Int32 nonQuery();

        public MessageActivityDao(AbstractConnection cxn)
        {
            _cxn = (MdoOracleConnection)cxn;
        }

        #region Message Activity Crud

        #region Create Message Activity
        internal MessageActivity createMessageActivity(MessageActivity activity)
        {
            OracleQuery query = buildCreateMessageActivityQuery(activity);
            nonQuery insertQuery = delegate() { return query.Command.ExecuteNonQuery(); };
            _cxn.query(query, insertQuery);
            activity.Id = ((Oracle.DataAccess.Types.OracleDecimal)query.Command.Parameters["outId"].Value).ToInt32();
            return activity;
        }

        internal OracleQuery buildCreateMessageActivityQuery(MessageActivity activity)
        {
            string sql = "INSERT INTO SMS.MESSAGE_ACTIVITY (SMS_USER_ID, ACTION, STATUS, PERFORMER_TYPE, DETAIL_VALUE, SECURE_MESSAGE_ID) VALUES " +
                "(:userId, :action, :status, :performerType, :detailValue, :smId) RETURNING ACTIVITY_ID INTO :outId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter userIdParam = new OracleParameter("userId", OracleDbType.Decimal);
            userIdParam.Value = Convert.ToDecimal(activity.UserId);
            query.Command.Parameters.Add(userIdParam);

            OracleParameter actionParam = new OracleParameter("action", OracleDbType.Decimal);
            actionParam.Value = Convert.ToDecimal(activity.Action);
            query.Command.Parameters.Add(actionParam);

            OracleParameter statusParam = new OracleParameter("status", OracleDbType.Decimal);
            statusParam.Value = Convert.ToDecimal(1); // all status values seem to be 1 so setting statically
            query.Command.Parameters.Add(statusParam);

            OracleParameter performerTypeParam = new OracleParameter("performerType", OracleDbType.Decimal);
            performerTypeParam.Value = Convert.ToDecimal(activity.PerformerType);
            query.Command.Parameters.Add(performerTypeParam);

            OracleParameter detailValueParam = new OracleParameter("detailValue", OracleDbType.Varchar2, 4000);
            if (String.IsNullOrEmpty(activity.Detail))
            {
                detailValueParam.Value = DBNull.Value;
            }
            else
            {
                detailValueParam.Value = activity.Detail;
            }
            query.Command.Parameters.Add(detailValueParam);

            OracleParameter smIdParam = new OracleParameter("smId", OracleDbType.Decimal);
            smIdParam.Value = Convert.ToDecimal(activity.MessageId);
            query.Command.Parameters.Add(smIdParam);

            OracleParameter outParam = new OracleParameter("outId", OracleDbType.Decimal);
            outParam.Direction = ParameterDirection.Output;
            query.Command.Parameters.Add(outParam);

            return query;
        }
        #endregion

        #endregion

    }
}
