using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Oracle.DataAccess.Client;
using System.Data;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.domain.sm;

namespace gov.va.medora.mdo.dao.oracle.mhv.sm
{
    public class AttachmentDao
    {
        MdoOracleConnection _cxn;
        delegate OracleDataReader reader();
        delegate Int32 nonQuery();

        public AttachmentDao(AbstractConnection cxn)
        {
            _cxn = (MdoOracleConnection)cxn;
        }

        #region Attachment CRUD
        #region Create Attachment
        public domain.sm.MessageAttachment createAttachment(string attachmentName, byte[] attachment, string mimeType)
        {
            OracleQuery request = buildCreateAttachmentQuery(attachmentName, attachment, mimeType);
            nonQuery qry = delegate() { return (Int32)request.Command.ExecuteNonQuery(); };
            if ((Int32)_cxn.query(request, qry) != 1)
            {
                throw new MdoException("Unable to insert new message attachment");
            }
            MessageAttachment result = new MessageAttachment() { AttachmentName = attachmentName, MimeType = mimeType };
            result.Id = ((Oracle.DataAccess.Types.OracleDecimal)request.Command.Parameters["outId"].Value).ToInt32();
            return result;
        }

        internal OracleQuery buildCreateAttachmentQuery(string attachmentName, byte[] attachment, string mimeType)
        {
            string sql = "INSERT INTO SMS.MESSAGE_ATTACHMENT (ATTACHMENT_NAME, ATTACHMENT, MIME_TYPE, CREATED_DATE, ACTIVE) VALUES " + 
                "(:attachmentName, :attachment, :mimeType, SYSTIMESTAMP, 1) RETURNING ATTACHMENT_ID INTO :outId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter attachmentNameParam = new OracleParameter("attachmentName", OracleDbType.Varchar2, 80);
            attachmentNameParam.Value = attachmentName;
            query.Command.Parameters.Add(attachmentNameParam);

            OracleParameter attachmentParam = new OracleParameter("attachment", OracleDbType.Blob);
            attachmentParam.Value = attachment;
            query.Command.Parameters.Add(attachmentParam);

            OracleParameter mimeTypeParam = new OracleParameter("mimeType", OracleDbType.Varchar2, 100);
            mimeTypeParam.Value = mimeType;
            query.Command.Parameters.Add(mimeTypeParam);

            OracleParameter outIdParam = new OracleParameter("outId", OracleDbType.Decimal);
            outIdParam.Direction = ParameterDirection.Output;
            query.Command.Parameters.Add(outIdParam);

            return query;
        }
        #endregion

        #region Get Attachment
        public domain.sm.MessageAttachment getAttachment(Int32 attachmentId)
        {
            OracleQuery request = buildGetAttachmentQuery(attachmentId);
            reader rdr = delegate() { return (OracleDataReader)request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, rdr);
            return toAttachment(response);
        }

        internal OracleQuery buildGetAttachmentQuery(Int32 attachmentId)
        {
            string sql = "SELECT ATTACHMENT_ID, ATTACHMENT_NAME, ATTACHMENT, MIME_TYPE, OPLOCK AS ATTOPLOCK FROM SMS.MESSAGE_ATTACHMENT WHERE ATTACHMENT_ID=:attachmentId AND ACTIVE=1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);
            query.Command.InitialLOBFetchSize = -1; // better performance - returns BLOB inline

            OracleParameter attachmentIdParam = new OracleParameter("attachmentId", OracleDbType.Decimal);
            attachmentIdParam.Value = attachmentId;
            query.Command.Parameters.Add(attachmentIdParam);

            return query;
        }

        internal MessageAttachment toAttachment(IDataReader rdr)
        {
            MessageAttachment ma = new MessageAttachment();

            if (rdr.Read())
            {
                ma = MessageAttachment.getAttachmentFromReader(rdr);
            }
            return ma;
        }
        #endregion

        #region Update Attachment
        public domain.sm.MessageAttachment updateAttachment(MessageAttachment attachment)
        {
            OracleQuery request = buildUpdateAttachmentQuery(attachment);
            nonQuery qry = delegate() { return (Int32)request.Command.ExecuteNonQuery(); };
            if ((Int32)_cxn.query(request, qry) != 1)
            {
                throw new MdoException("Unable to update message attachment");
            }
            attachment.Oplock++;
            return attachment;
        }

        public OracleQuery buildUpdateAttachmentQuery(MessageAttachment attachment)
        {
            string sql = "UPDATE SMS.MESSAGE_ATTACHMENT SET ATTACHMENT_NAME=:attachmentName, ATTACHMENT=:attachment, MIME_TYPE=:mimeType, " +
                "OPLOCK=:oplockPlusOne WHERE ATTACHMENT_ID=:attachmentId AND OPLOCK=:oplock";
            
            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter attachmentNameParam = new OracleParameter("attachmentName", OracleDbType.Varchar2, 80);
            attachmentNameParam.Value = attachment.AttachmentName;
            query.Command.Parameters.Add(attachmentNameParam);

            OracleParameter attachmentParam = new OracleParameter("attachment", OracleDbType.Blob);
            attachmentParam.Value = attachment.SmFile;
            query.Command.Parameters.Add(attachmentParam);

            OracleParameter mimeTypeParam = new OracleParameter("mimeType", OracleDbType.Varchar2, 100);
            mimeTypeParam.Value = attachment.MimeType;
            query.Command.Parameters.Add(mimeTypeParam);

            OracleParameter oplockPlusOneParam = new OracleParameter("oplockPlusOne", OracleDbType.Decimal);
            oplockPlusOneParam.Value = attachment.Oplock + 1;
            query.Command.Parameters.Add(oplockPlusOneParam);

            OracleParameter attachmentIdParam = new OracleParameter("attachmentId", OracleDbType.Decimal);
            attachmentIdParam.Value = attachment.Id;
            query.Command.Parameters.Add(attachmentIdParam);

            OracleParameter oplockParam = new OracleParameter("oplock", OracleDbType.Decimal);
            oplockParam.Value = attachment.Oplock;
            query.Command.Parameters.Add(oplockParam);

            return query;
        }
        #endregion

        #region Delete Attachment
        public void deleteAttachment(Int32 attachmentId)
        {
            OracleQuery request = buildDeleteAttachmentQuery(attachmentId);
            nonQuery qry = delegate() { return (Int32)request.Command.ExecuteNonQuery(); };
            if ((Int32)_cxn.query(request, qry) != 1)
            {
                throw new MdoException("Unable to delete message attachment");
            }
        }

        internal OracleQuery buildDeleteAttachmentQuery(Int32 attachmentId)
        {
            string sql = "DELETE FROM SMS.MESSAGE_ATTACHMENT WHERE ATTACHMENT_ID=:attachmentId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter attachmentIdParam = new OracleParameter("attachmentId", OracleDbType.Decimal);
            attachmentIdParam.Value = attachmentId;
            query.Command.Parameters.Add(attachmentIdParam);

            return query;
        }

        #endregion

        #endregion

        #region Attachment Transactions
        #region Add Attachment
        public MessageAttachment attachToMessage(MessageAttachment attachment, Message message)
        {
            try
            {
                _cxn.beginTransaction();

                if (attachment.Id > 0)
                {
                    attachment = updateAttachment(attachment); // could simply rely on updateAttachment function but thought this might add a level of convenience
                }
                else if (attachment.Id <= 0)
                {
                    // create attachment
                    attachment = createAttachment(attachment.AttachmentName, attachment.SmFile, attachment.MimeType);
                    // update message - set attachment ID properties
                    message = updateMessageAttachmentFields(message, attachment.Id);
                }

                _cxn.commitTransaction();
                return attachment;
            }
            catch (Exception)
            {
                _cxn.rollbackTransaction();
                throw;
            }
        }

        internal Message updateMessageAttachmentFields(Message message, Int32 attachmentId)
        {
            OracleQuery request = buildUpdateMessageQuery(message, attachmentId);
            nonQuery qry = delegate() { return (Int32)request.Command.ExecuteNonQuery(); };
            if ((Int32)_cxn.query(request, qry) != 1)
            {
                throw new MdoException("Failed to update secure message record for attachment");
            }
            message.Attachment = true;
            message.AttachmentId = attachmentId;
            message.Oplock++;
            return message;
        }

        internal OracleQuery buildUpdateMessageQuery(Message message, Int32 attachmentId)
        {
            string sql = "";
            if (attachmentId > 0)
            {
                sql = "UPDATE SMS.SECURE_MESSAGE SET OPLOCK = :oplockPlusOne, MODIFIED_DATE = SYSDATE, " +
                    "HAS_ATTACHMENT = 1, ATTACHMENT_ID = :attachmentId WHERE SECURE_MESSAGE_ID = :secureMessageId AND OPLOCK = :oplock";
            }
            else
            {
                sql = "UPDATE SMS.SECURE_MESSAGE SET OPLOCK = :oplockPlusOne, MODIFIED_DATE = SYSDATE, " +
                    "HAS_ATTACHMENT = 0, ATTACHMENT_ID = :attachmentId WHERE SECURE_MESSAGE_ID = :secureMessageId AND OPLOCK = :oplock";
            }

            OracleQuery query = new OracleQuery();
            OracleCommand command = new OracleCommand(sql);
            query.Command = command;

            OracleParameter oplockPlusOneParam = new OracleParameter("oplockPlusOne", OracleDbType.Decimal);
            oplockPlusOneParam.Value = Convert.ToDecimal(message.Oplock + 1);
            query.Command.Parameters.Add(oplockPlusOneParam);

            //OracleParameter modifiedParam = new OracleParameter("modifiedDate", OracleDbType.Date);
            //modifiedParam.Value = new Oracle.DataAccess.Types.OracleDate(DateTime.Now);
            //query.Command.Parameters.Add(modifiedParam);

            OracleParameter attachmentIdParam = new OracleParameter("attachmentId", OracleDbType.Decimal);
            if (attachmentId > 0)
            {
                attachmentIdParam.Value = Convert.ToDecimal(attachmentId);
            }
            else
            {
                attachmentIdParam.Value = DBNull.Value;
            }
            query.Command.Parameters.Add(attachmentIdParam);

            OracleParameter idParam = new OracleParameter("secureMessageId", OracleDbType.Decimal);
            idParam.Value = Convert.ToDecimal(message.Id);
            query.Command.Parameters.Add(idParam);

            OracleParameter oplockParam = new OracleParameter("oplock", OracleDbType.Decimal);
            oplockParam.Value = Convert.ToDecimal(message.Oplock);
            query.Command.Parameters.Add(oplockParam);

            return query;
        }
        #endregion

        #region Delete Attachment
        public Message deleteAttachmentFromMessage(Int32 messageId)
        {
            SecureMessageDao smDao = new SecureMessageDao(_cxn);
            Message dbMsg = smDao.getMessage(messageId);
            if (dbMsg == null || dbMsg.Id <= 0 || !dbMsg.Attachment || dbMsg.AttachmentId <= 0)
            {
                throw new MdoException("Not a valid message ID");
            }

            try
            {
                _cxn.beginTransaction();

                OracleQuery request = buildUpdateMessageQuery(dbMsg, -1);
                nonQuery qry = delegate() { return (Int32)request.Command.ExecuteNonQuery(); };
                if ((Int32)_cxn.query(request, qry) != 1)
                {
                    throw new MdoException("Failed to update secure message record for attachment");
                }
                deleteAttachment(Convert.ToInt32(dbMsg.AttachmentId));
                dbMsg.AttachmentId = 0;
                dbMsg.Attachment = false;
                dbMsg.Oplock++;
                _cxn.commitTransaction();
                return dbMsg;
            }
            catch (Exception)
            {
                _cxn.rollbackTransaction();
                throw;
            }
        }
        #endregion
        #endregion
    }
}
