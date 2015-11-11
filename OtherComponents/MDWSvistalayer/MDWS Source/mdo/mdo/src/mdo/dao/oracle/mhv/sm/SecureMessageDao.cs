using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm;
//using System.Data.OracleClient;
using Oracle.DataAccess.Client;

using System.Data;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.oracle.mhv.sm
{
    public class SecureMessageDao
    {
        MdoOracleConnection _cxn;
        delegate OracleDataReader reader();
        delegate Int32 nonQuery();

        public SecureMessageDao(AbstractConnection cxn)
        {
            _cxn = (MdoOracleConnection)cxn;
        }


        #region Get Entire Message (w/ Thread & Addressees)

        public Message getMessageComplete(Int32 messageId)
        {
            OracleQuery request = buildGetMessageCompleteQuery(messageId);
            reader rdr = delegate() { return request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, rdr);
            Message msgComplete = toMessageComplete(response);
            Message msgBody = getSecureMessageBody(messageId);
            msgComplete.Body = msgBody.Body;
            msgComplete.Checksum = msgBody.Checksum;
            return msgComplete;
        }

        internal OracleQuery buildGetMessageCompleteQuery(Int32 messageId)
        {
            string sql = "SELECT SM.SECURE_MESSAGE_ID, SM.CLINICIAN_STATUS, SM.COMPLETED_DATE, SM.ASSIGNED_TO, SM.OPLOCK AS SMOPLOCK, SM.ESCALATED, SM.SENT_DATE, SM.SENDER_TYPE, " +
                "SM.SENDER_ID, SM.SENDER_NAME, SM.RECIPIENT_ID, SM.RECIPIENT_TYPE, SM.RECIPIENT_ID, SM.RECIPIENT_NAME, SM.HAS_ATTACHMENT, SM.ATTACHMENT_ID, " + 
                "MT.THREAD_ID, MT.SUBJECT, MT.TRIAGE_GROUP_ID, MT.OPLOCK AS MTOPLOCK, MT.CATEGORY_TYPE, " +
                "ADDR.ADDRESSEE_ID, ADDR.ADDRESSEE_ROLE, ADDR.USER_ID, ADDR.OPLOCK AS ADDROPLOCK, ADDR.FOLDER_ID, ADDR.READ_DATE, ADDR.REMINDER_DATE FROM SMS.SECURE_MESSAGE SM JOIN SMS.MESSAGE_THREAD MT ON " +
                "SM.THREAD_ID=MT.THREAD_ID RIGHT JOIN SMS.ADDRESSEE ADDR ON SM.SECURE_MESSAGE_ID=ADDR.SECURE_MESSAGE_ID WHERE SM.SECURE_MESSAGE_ID=:messageId AND SM.ACTIVE=1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter messageIdParam = new OracleParameter("messageId", OracleDbType.Decimal);
            messageIdParam.Value = messageId;
            query.Command.Parameters.Add(messageIdParam);

            return query;
        }

        internal Message toMessageComplete(IDataReader rdr)
        {
            Message result = new Message();

            if (rdr.Read())
            {
                result = Message.getMessageFromReader(rdr);
                Thread t = Thread.getThreadFromReader(rdr);
                result.MessageThread = t;
                
                result.Addressees = new List<Addressee>();
                Addressee a = Addressee.getAddresseeFromReader(rdr);
                result.Addressees.Add(a); // need to add the Addressee from the current IDataReader.Read() before we loop through others
                while (rdr.Read())
                {
                    result.Addressees.Add(Addressee.getAddresseeFromReader(rdr));
                }
            }
            return result;
        }

        #endregion

        #region Get Messages In Thread
        public Thread getMessagesFromThread(Int32 threadId)
        {
            OracleQuery request = buildGetMessagesFromThreadQuery(threadId);
            reader rdr = delegate() { return (OracleDataReader)request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, rdr);
            return toMessagesFromThread(response);
        }

        internal OracleQuery buildGetMessagesFromThreadQuery(Int32 threadId)
        {
            string sql = "SELECT SM.SECURE_MESSAGE_ID, SM.CLINICIAN_STATUS, SM.COMPLETED_DATE, SM.ASSIGNED_TO, SM.OPLOCK AS SMOPLOCK, " +
                "SM.SENT_DATE, SM.SENDER_ID, SM.RECIPIENT_ID, MT.SUBJECT, MT.TRIAGE_GROUP_ID, MT.OPLOCK AS MTOPLOCK, MT.CATEGORY_TYPE " +
                "FROM SMS.SECURE_MESSAGE SM JOIN SMS.MESSAGE_THREAD MT ON SM.THREAD_ID=MT.THREAD_ID WHERE SM.THREAD_ID=:threadId AND SM.ACTIVE=1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter threadIdParam = new OracleParameter("threadId", OracleDbType.Decimal);
            threadIdParam.Value = threadId;
            query.Command.Parameters.Add(threadIdParam);

            return query;
        }

        public Thread toMessagesFromThread(IDataReader rdr)
        {
            Thread results = null;
            Dictionary<string, bool> messageTable = QueryUtils.getColumnExistsTable(TableSchemas.SECURE_MESSAGE_COLUMNS, rdr);

            while (rdr.Read())
            {
                if (results == null)
                {
                    results = Thread.getThreadFromReader(rdr);
                }
                if (results.Messages == null)
                {
                    results.Messages = new List<Message>();
                }
                results.Messages.Add(Message.getMessageFromReader(rdr, messageTable));
            }

            return results;
        }
        #endregion

        #region Get Message Body & Checksum
        public Message getSecureMessageBody(Int32 messageId)
        {
            OracleQuery query = buildGetSecureMessageBodyQuery(messageId);
            reader executeReader = delegate() { return query.Command.ExecuteReader(); };
            OracleDataReader reader = (OracleDataReader)_cxn.query(query, executeReader);
            return toMessageBodyAndChecksum(reader);
        }


        internal OracleQuery buildGetSecureMessageBodyQuery(Int32 messageId)
        {
            //string sql = "SELECT SM.SECURE_MESSAGE_ID, SM.CLINICIAN_STATUS, SM.COMPLETED_DATE, SM.ASSIGNED_TO, " +
            //    "SM.CHECKSUM, SM.THREAD_ID, SM.STATUS_SET_BY, SM.ACTIVE, SM.CREATED_DATE, SM.MODIFIED_DATE, " +
            //    "SM.ESCALATED, SM.SENT_DATE, SM.SENDER_TYPE, SM.SENDER_ID, SM.SENDER_NAME, " + 
            //    "SM.RECIPIENT_TYPE, SM.RECIPIENT_ID, SM.RECIPIENT_NAME, SM.BODY, " +
            //    "SM.ESCALATION_NOTIFICATION_DATE, SM.ESCALATION_NOTIFICATION_TRIES, SM.READ_RECEIPT, " + 
            //    "SM.HAS_ATTACHMENT, SM.ATTACHMENT_ID, MT.SUBJECT, MT.TRIAGE_GROUP_ID, MT.CATEGORY_TYPE " +
            //    "FROM SMS.secure_message SM JOIN SMS.message_thread MT ON " +
            //    "SM.THREAD_ID=MT.THREAD_ID WHERE SM.SECURE_MESSAGE_ID = :secureMessageId";
            string sql = "SELECT SM.CHECKSUM, SM.BODY FROM SMS.SECURE_MESSAGE SM WHERE SM.SECURE_MESSAGE_ID = :secureMessageId AND SM.ACTIVE = 1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);
            query.Command.InitialLOBFetchSize = -1; // setting this to -1 causes the SM.BODY column to be fetched inline - 10X performance increase

            OracleParameter idParam = new OracleParameter("secureMessageId", OracleDbType.Decimal);
            idParam.Value = Convert.ToDecimal(messageId);
            query.Command.Parameters.Add(idParam);

            return query;
        }

        internal Message toMessageBodyAndChecksum(IDataReader rdr)
        {
            Message msg = new Message();

            if (rdr.Read())
            {
                msg = Message.getMessageFromReader(rdr);
            }

            return msg;
        }
        #endregion

        #region Get All Messages Paged
        public IList<Thread> getSecureMessages(Int32 userId)
        {
            return getSecureMessages(userId, 0, 25);
        }

        public IList<Thread> getSecureMessages(Int32 userId, int pageStart, int pageSize)
        {
            OracleQuery query = buildGetSecureMessagesQuery(userId, pageStart, pageSize);
            reader executeReader = delegate() { return query.Command.ExecuteReader(); };
            OracleDataReader reader = (OracleDataReader)_cxn.query(query, executeReader);
            return toMessageThreads(reader);
        }


        /// <remarks>
        /// This SQL statement should work for all SMS users (patients, providers, etc).
        /// </remarks>
        internal OracleQuery buildGetSecureMessagesQuery(Int32 userId, int pageStart, int pageSize)
        {
            // not currently fetching body inline - should be able to add "SM.BODY" to statement below and begin receiving inline
            StringBuilder sb = new StringBuilder("SELECT ADDR.ADDRESSEE_ID, ADDR.ADDRESSEE_ROLE, ADDR.OPLOCK AS ADDROPLOCK, ADDR.ACTIVE AS ADDRACTIVE, ");
            sb.Append("ADDR.USER_ID, ADDR.CREATED_DATE AS ADDRCREATEDDATE, ADDR.MODIFIED_DATE AS ADDRMODIFIEDDATE, ADDR.FOLDER_ID, ");
            sb.Append("ADDR.READ_DATE, ADDR.REMINDER_DATE, FOLD.FOLDER_NAME, FOLD.ACTIVE AS FOLDACTIVE, FOLD.OPLOCK AS FOLDOPLOCK, ");
            sb.Append("SM.SECURE_MESSAGE_ID, SM.CLINICIAN_STATUS, SM.COMPLETED_DATE, SM.ASSIGNED_TO, ");
            sb.Append("SM.CHECKSUM, SM.THREAD_ID, SM.STATUS_SET_BY, SM.OPLOCK AS SMOPLOCK, SM.ACTIVE, SM.CREATED_DATE, SM.MODIFIED_DATE, ");
            sb.Append("SM.ESCALATED, SM.SENT_DATE, SM.SENDER_TYPE, SM.SENDER_ID, SM.SENDER_NAME, ");
            sb.Append("SM.RECIPIENT_TYPE, SM.RECIPIENT_ID, SM.RECIPIENT_NAME, ");
            sb.Append("SM.ESCALATION_NOTIFICATION_DATE, SM.ESCALATION_NOTIFICATION_TRIES, SM.READ_RECEIPT, ");
            sb.Append("SM.HAS_ATTACHMENT, SM.ATTACHMENT_ID, MT.SUBJECT, MT.TRIAGE_GROUP_ID, MT.CATEGORY_TYPE, MT.OPLOCK AS MTOPLOCK ");
            sb.Append("FROM SMS.ADDRESSEE ADDR ");
            sb.Append("JOIN SMS.SECURE_MESSAGE SM ON ADDR.SECURE_MESSAGE_ID=SM.SECURE_MESSAGE_ID ");
            sb.Append("JOIN SMS.MESSAGE_THREAD MT ON SM.THREAD_ID=MT.THREAD_ID ");
            sb.Append("LEFT JOIN SMS.FOLDER FOLD ON ADDR.FOLDER_ID=FOLD.FOLDER_ID ");
            sb.Append("WHERE ADDR.USER_ID = :userId AND ADDR.ACTIVE = 1 AND ROWNUM >= :pageStart AND ROWNUM <= :pageSize ");
            sb.Append("ORDER BY SM.SENT_DATE DESC");

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sb.ToString());
            query.Command.InitialLOBFetchSize = -1; // setting this to -1 causes the SM.BODY column to be fetched inline - 10X performance increase

            OracleParameter idParam = new OracleParameter("userId", OracleDbType.Decimal);
            idParam.Value = Convert.ToDecimal(userId);
            query.Command.Parameters.Add(idParam);

            OracleParameter pageStartParam = new OracleParameter("pageStart", OracleDbType.Decimal);
            pageStartParam.Value = Convert.ToDecimal(pageStart);
            query.Command.Parameters.Add(pageStartParam);

            OracleParameter pageSizeParam = new OracleParameter("pageSize", OracleDbType.Decimal);
            if (pageSize == 0)
            {
                pageSize = 25; // set default to 25
            }
            pageSizeParam.Value = Convert.ToDecimal(pageStart + pageSize);
            query.Command.Parameters.Add(pageSizeParam);

            return query;
        }

        internal IList<domain.sm.Thread> toMessageThreads(IDataReader rdr)
        {
            Dictionary<Int32, Thread> threads = new Dictionary<Int32, Thread>();
            Dictionary<string, bool> messageColumnTable = QueryUtils.getColumnExistsTable(TableSchemas.SECURE_MESSAGE_COLUMNS, rdr);
            Dictionary<string, bool> threadColumnTable = QueryUtils.getColumnExistsTable(TableSchemas.MESSAGE_THREAD_COLUMNS, rdr);
            Dictionary<string, bool> addresseeColumnTable = QueryUtils.getColumnExistsTable(TableSchemas.ADDRESSEE_COLUMNS, rdr);
            Dictionary<string, bool> folderColumnTable = QueryUtils.getColumnExistsTable(TableSchemas.FOLDER_COLUMNS, rdr);

            while (rdr.Read())
            {
                Int32 threadId = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("THREAD_ID")));
                if (!threads.ContainsKey(threadId))
                {
                    Thread newThread = Thread.getThreadFromReader(rdr, threadColumnTable);
                    newThread.Messages = new List<Message>();
                    threads.Add(threadId, newThread);
                }
                Thread currentThread = threads[threadId];

                if (currentThread.Messages == null)
                {
                    currentThread.Messages = new List<Message>();
                }

                Message msg = Message.getMessageFromReader(rdr, messageColumnTable);

                if (msg.Addressees == null)
                {
                    msg.Addressees = new List<Addressee>();
                }
                Addressee addressee = Addressee.getAddresseeFromReader(rdr, addresseeColumnTable);
                msg.Addressees.Add(addressee);

                Folder folder = Folder.getFolderFromReader(rdr, folderColumnTable);
                addressee.FolderId = folder.Id;
                addressee.Folder = folder;

                msg.MessageThread = currentThread;
                currentThread.Messages.Add(msg);
            }

            Thread[] result = threads.Values.ToArray<Thread>();
            for (int i = 0; i < result.Length; i++)
            {
                result[i].Messages.Sort(); // even though the SQL statement sorts, it puts NULL values first which are turned in to 1/1/0001 timstamps
                result[i].Messages.Reverse();
            }
            return result;
        }

        #endregion

        #region Get Messages By Folder
        public IList<Thread> getMessagesInFolder(Int32 userId, Int32 folderId)
        {
            return getMessagesInFolder(userId, folderId, 0, 25);
        }

        public IList<Thread> getMessagesInFolder(Int32 userId, Int32 folderId, int pageStart, int pageSize)
        {
            OracleQuery query = buildGetMessagesInFolderQuery(userId, folderId, pageStart, pageSize);
            reader executeReader = delegate() { return query.Command.ExecuteReader(); };
            OracleDataReader reader = (OracleDataReader)_cxn.query(query, executeReader);
            return toMessageThreads(reader);
        }

        /// <remarks>
        /// This SQL statement should work for all SMS users (patients, providers, etc).
        /// </remarks>
        internal OracleQuery buildGetMessagesInFolderQuery(Int32 userId, Int32 folderId, int pageStart, int pageSize)
        {
            // not currently fetching body inline - should be able to add "SM.BODY" to statement below and begin receiving inline
            StringBuilder sb = new StringBuilder("SELECT * FROM (SELECT SUBQUERYTABLE.*, ROWNUM AS MYROWNUM FROM (SELECT ADDR.ADDRESSEE_ID, ADDR.ADDRESSEE_ROLE, "); 
            sb.Append("ADDR.OPLOCK AS ADDROPLOCK, ADDR.ACTIVE AS ADDRACTIVE, ");
            sb.Append("ADDR.USER_ID, ADDR.CREATED_DATE AS ADDRCREATEDDATE, ADDR.MODIFIED_DATE AS ADDRMODIFIEDDATE, ADDR.FOLDER_ID, ");
            sb.Append("ADDR.READ_DATE, ADDR.REMINDER_DATE, FOLD.FOLDER_NAME, FOLD.ACTIVE AS FOLDACTIVE, FOLD.OPLOCK AS FOLDOPLOCK, ");
            sb.Append("SM.SECURE_MESSAGE_ID, SM.CLINICIAN_STATUS, SM.COMPLETED_DATE, SM.ASSIGNED_TO, ");
            sb.Append("SM.CHECKSUM, SM.THREAD_ID, SM.STATUS_SET_BY, SM.OPLOCK AS SMOPLOCK, SM.ACTIVE, SM.CREATED_DATE, SM.MODIFIED_DATE, ");
            sb.Append("SM.ESCALATED, SM.SENT_DATE, SM.SENDER_TYPE, SM.SENDER_ID, SM.SENDER_NAME, ");
            sb.Append("SM.RECIPIENT_TYPE, SM.RECIPIENT_ID, SM.RECIPIENT_NAME, ");
            sb.Append("SM.ESCALATION_NOTIFICATION_DATE, SM.ESCALATION_NOTIFICATION_TRIES, SM.READ_RECEIPT, ");
            sb.Append("SM.HAS_ATTACHMENT, SM.ATTACHMENT_ID, MT.SUBJECT, MT.TRIAGE_GROUP_ID, MT.CATEGORY_TYPE, MT.OPLOCK AS MTOPLOCK ");
            sb.Append("FROM SMS.ADDRESSEE ADDR ");
            sb.Append("JOIN SMS.SECURE_MESSAGE SM ON ADDR.SECURE_MESSAGE_ID=SM.SECURE_MESSAGE_ID ");
            sb.Append("JOIN SMS.MESSAGE_THREAD MT ON SM.THREAD_ID=MT.THREAD_ID ");
            sb.Append("LEFT JOIN SMS.FOLDER FOLD ON ADDR.FOLDER_ID=FOLD.FOLDER_ID ");
            sb.Append("WHERE ADDR.USER_ID = :userId AND ADDR.FOLDER_ID = :folderId AND ADDR.ACTIVE = 1 ORDER BY SM.SENT_DATE DESC, ADDR.ADDRESSEE_ID) SUBQUERYTABLE "); 
            sb.Append("WHERE ROWNUM <= :pageSize) WHERE MYROWNUM >= :pageStart");

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sb.ToString());
            query.Command.InitialLOBFetchSize = -1; // setting this to -1 causes the SM.BODY column to be fetched inline - 10X performance increase

            OracleParameter idParam = new OracleParameter("userId", OracleDbType.Decimal);
            idParam.Value = Convert.ToDecimal(userId);
            query.Command.Parameters.Add(idParam);

            OracleParameter folderIdParam = new OracleParameter("folderId", OracleDbType.Decimal);
            folderIdParam.Value = Convert.ToDecimal(folderId);
            query.Command.Parameters.Add(folderIdParam);

            OracleParameter pageSizeParam = new OracleParameter("pageSize", OracleDbType.Decimal);
            if (pageSize == 0)
            {
                pageSize = 25; // set default to 25
            }
            pageSizeParam.Value = Convert.ToDecimal(pageStart + pageSize);
            query.Command.Parameters.Add(pageSizeParam);

            OracleParameter pageStartParam = new OracleParameter("pageStart", OracleDbType.Decimal);
            pageStartParam.Value = Convert.ToDecimal(pageStart);
            query.Command.Parameters.Add(pageStartParam);

            return query;
        }
        #endregion

        #region Thread CRUD

        #region Create Thread
        internal domain.sm.Thread createThread(domain.sm.Thread thread)
        {
            OracleQuery query = buildCreateThreadQuery(thread);

            nonQuery nq = delegate() { return query.Command.ExecuteNonQuery(); };
            Int32 rowsAffected = (Int32)_cxn.query(query, nq);

            if (rowsAffected == 1)
            {
                thread.Id = ((Oracle.DataAccess.Types.OracleDecimal)query.Command.Parameters["outId"].Value).ToInt32();
                return thread;
            }
            else
            {
                throw new MdoException("Unexpected error creating new Secure Message thread!");
            }
        }

        // GOOD TO GO
        internal OracleQuery buildCreateThreadQuery(domain.sm.Thread thread)
        {
            string sql = "INSERT INTO SMS.MESSAGE_THREAD (SUBJECT, TRIAGE_GROUP_ID, CREATED_DATE, MODIFIED_DATE, " + 
                "CATEGORY_TYPE) VALUES (:subject, :triageGroupId, SYSDATE, SYSDATE, :categoryType) " +
                "RETURNING THREAD_ID INTO :outId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter subjectParam = new OracleParameter("subject", OracleDbType.Varchar2, 512);
            subjectParam.Value = thread.Subject;
            query.Command.Parameters.Add(subjectParam);

            OracleParameter triageGroupParam = new OracleParameter("triageGroupId", OracleDbType.Decimal);
            if (thread.MailGroup == null || thread.MailGroup.Id <= 0)
            {
                triageGroupParam.Value = DBNull.Value;
            }
            else
            {
                triageGroupParam.Value = thread.MailGroup.Id;
            }
            query.Command.Parameters.Add(triageGroupParam);

            // using Oracle server time
            //OracleParameter createdDateParam = new OracleParameter("createdDate", OracleDbType.Date);
            //createdDateParam.Value = "getdate()"; // new Oracle.DataAccess.Types.OracleDate(DateTime.Now);
            //query.Command.Parameters.Add(createdDateParam);

            //OracleParameter modifiedDateParam = new OracleParameter("modifiedDate", OracleDbType.Date);
            //modifiedDateParam.Value = "getdate()"; // new Oracle.DataAccess.Types.OracleDate(DateTime.Now);
            //query.Command.Parameters.Add(modifiedDateParam);

            OracleParameter categoryTypeParam = new OracleParameter("categoryType", OracleDbType.Decimal);
            categoryTypeParam.Value = (Int32)thread.MessageCategoryType;
            query.Command.Parameters.Add(categoryTypeParam);

            OracleParameter outParam = new OracleParameter("outId", OracleDbType.Decimal);
            outParam.Direction = ParameterDirection.Output;
            query.Command.Parameters.Add(outParam);

            return query;
        }
        #endregion

        #region Update Thread
        public Thread updateThread(Thread thread)
        {
            OracleQuery request = buildUpdateThreadQuery(thread);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 rowsAffected = (Int32)_cxn.query(request, qry);

            if (rowsAffected != 1)
            {
                throw new MdoException("Expected one record to be deleted. Rows affected: " + rowsAffected.ToString());
            }

            thread.Oplock++;
            return thread;
        }

        internal OracleQuery buildUpdateThreadQuery(domain.sm.Thread thread)
        {
            string sql = "UPDATE SMS.MESSAGE_THREAD SET SUBJECT=:subject, TRIAGE_GROUP_ID=:triageGroupId, OPLOCK=:oplockPlusOne, MODIFIED_DATE = SYSDATE, " +
                "CATEGORY_TYPE=:categoryType WHERE THREAD_ID=:threadId AND OPLOCK=:oplock";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter subjectParam = new OracleParameter("subject", OracleDbType.Varchar2, 512);
            subjectParam.Value = thread.Subject;
            query.Command.Parameters.Add(subjectParam);

            OracleParameter triageGroupParam = new OracleParameter("triageGroupId", OracleDbType.Decimal);
            if (thread.MailGroup == null || thread.MailGroup.Id <= 0)
            {
                triageGroupParam.Value = DBNull.Value;
            }
            else
            {
                triageGroupParam.Value = thread.MailGroup.Id;
            }
            query.Command.Parameters.Add(triageGroupParam);

            OracleParameter oplockPlusOneParam = new OracleParameter("oplockPlusOne", OracleDbType.Decimal);
            oplockPlusOneParam.Value = Convert.ToDecimal(thread.Oplock + 1);
            query.Command.Parameters.Add(oplockPlusOneParam);

            //OracleParameter modifiedDateParam = new OracleParameter("modifiedDate", OracleDbType.Date);
            //modifiedDateParam.Value = new Oracle.DataAccess.Types.OracleDate(DateTime.Now);
            //query.Command.Parameters.Add(modifiedDateParam);

            OracleParameter categoryTypeParam = new OracleParameter("categoryType", OracleDbType.Decimal);
            categoryTypeParam.Value = (Int32)thread.MessageCategoryType;
            query.Command.Parameters.Add(categoryTypeParam);

            OracleParameter threadIdParam = new OracleParameter("threadId", OracleDbType.Decimal);
            threadIdParam.Value = Convert.ToDecimal(thread.Id);
            query.Command.Parameters.Add(threadIdParam);

            OracleParameter oplockParam = new OracleParameter("oplock", OracleDbType.Decimal);
            oplockParam.Value = Convert.ToDecimal(thread.Oplock);
            query.Command.Parameters.Add(oplockParam);

            return query;
        }

        #endregion

        #region Delete Thread
        internal void deleteThread(Int32 threadId)
        {
            deleteThread(threadId, false);
        }

        internal void deleteThread(Int32 threadId, bool inactivate)
        {
            OracleQuery request = buildDeleteThreadQuery(threadId, inactivate);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 rowsAffected = (Int32)_cxn.query(request, qry);

            if (rowsAffected != 1)
            {
                throw new MdoException("Expected one record to be deleted. Rows affected: " + rowsAffected.ToString());
            }
        }

        internal OracleQuery buildDeleteThreadQuery(Int32 threadId, bool inactivate)
        {
            string sql = "DELETE FROM SMS.MESSAGE_THREAD WHERE THREAD_ID=:threadId";
            
            if (inactivate)
            {
                // TBD - which is the correct way? delete the record or set to inactive?
                sql = "UPDATE SMS.MESSAGE_THREAD SET ACTIVE=0 WHERE THREAD_ID=:threadId";
            }

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter threadIdParam = new OracleParameter("threadId", OracleDbType.Decimal);
            threadIdParam.Value = Convert.ToDecimal(threadId);
            query.Command.Parameters.Add(threadIdParam);

            return query;
        }
        #endregion

        #region Get Thread
        public domain.sm.Thread getThread(Int32 threadId)
        {
            OracleQuery request = buildGetThreadQuery(threadId);
            reader requestRdr = delegate() { return request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, requestRdr);
            return toThread(response);
        }

        internal OracleQuery buildGetThreadQuery(int threadId)
        {
            string sql = "SELECT * FROM SMS.MESSAGE_THREAD WHERE THREAD_ID=:threadId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter threadIdParam = new OracleParameter("threadId", OracleDbType.Decimal);
            threadIdParam.Value = Convert.ToDecimal(threadId);
            query.Command.Parameters.Add(threadIdParam);

            return query;
        }

        internal domain.sm.Thread toThread(IDataReader rdr)
        {
            domain.sm.Thread thread = new domain.sm.Thread();

            if (rdr.Read())
            {
                thread = Thread.getThreadFromReader(rdr);
            }
            return thread;
        }
        #endregion
        #endregion

        #region Message CRUD
        #region Create Message
        public domain.sm.Message createMessage(domain.sm.Message message)
        {
            OracleQuery query = buildCreateMessageQuery(message);
            nonQuery nq = delegate() { return query.Command.ExecuteNonQuery(); };
            Int32 rowsAffected = (Int32)_cxn.query(query, nq);
            message.Id = ((Oracle.DataAccess.Types.OracleDecimal)query.Command.Parameters["outId"].Value).ToInt32();

            return message;
        }

        internal OracleQuery buildCreateMessageQuery(domain.sm.Message message)
        {
            string sql = "INSERT INTO SMS.SECURE_MESSAGE (CLINICIAN_STATUS, COMPLETED_DATE, " +
                "ASSIGNED_TO, CHECKSUM, THREAD_ID, STATUS_SET_BY, MODIFIED_DATE, " +
                "ESCALATED, BODY, SENT_DATE, SENDER_TYPE, SENDER_ID, SENDER_NAME, RECIPIENT_TYPE, " +
                "RECIPIENT_ID, RECIPIENT_NAME, SENT_DATE_LOCAL, ESCALATION_NOTIFICATION_DATE, " +
                "ESCALATION_NOTIFICATION_TRIES, READ_RECEIPT, HAS_ATTACHMENT, ATTACHMENT_ID) VALUES (" +
                ":clinicianStatus, :completedDate, :assignedTo, :checksum, :threadId, :statusSetBy, " +
                "SYSDATE, :escalated, :body, :sentDate, :senderType, :senderId, " +
                ":senderName, :recipientType, :recipientId, :recipientName, :sentDateLocal, " +
                ":escalationNotificationDate, :escalationNotificationTries, :readReceipt, :hasAttachment, :attachmentId) " +
                "RETURNING SECURE_MESSAGE_ID INTO :outId";

            OracleQuery query = new OracleQuery();
            OracleCommand command = new OracleCommand(sql);
            query.Command = command;

            buildMessageCommand(query.Command, message);

            // add out ID - not in helper function
            OracleParameter outParam = new OracleParameter("outId", OracleDbType.Decimal);
            outParam.Direction = ParameterDirection.Output;
            command.Parameters.Add(outParam);

            return query;
        }
        #endregion

        #region Update Message
        public domain.sm.Message updateMessage(domain.sm.Message message)
        {
            OracleQuery request = buildUpdateMessageQuery(message);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 rowsAffected = (Int32)_cxn.query(request, qry);

            if (rowsAffected != 1)
            {
                throw new MdoException("Expected one record to be updated. Rows affected: " + rowsAffected.ToString());
            }

            message.Oplock++;
            return message;
        }


        internal OracleQuery buildUpdateMessageQuery(Message message)
        {
            string sql = "UPDATE SMS.SECURE_MESSAGE SET OPLOCK = :oplockPlusOne, CLINICIAN_STATUS = :clinicianStatus, COMPLETED_DATE = :completedDate, " +
                "ASSIGNED_TO = :assignedTo, CHECKSUM = :checksum, THREAD_ID = :threadId, STATUS_SET_BY = :statusSetBy, " +
                "MODIFIED_DATE = SYSDATE, ESCALATED = :escalated, BODY = :body, SENT_DATE = :sentDate, SENDER_TYPE = :senderType, " +
                "SENDER_ID = :senderId, SENDER_NAME = :senderName, RECIPIENT_TYPE = :recipientType, RECIPIENT_ID = :recipientId, " +
                "RECIPIENT_NAME = :recipientName, SENT_DATE_LOCAL = :sentDateLocal, ESCALATION_NOTIFICATION_DATE = :escalationNotificationDate, " +
                "ESCALATION_NOTIFICATION_TRIES = :escalationNotificationTries, READ_RECEIPT = :readReceipt, HAS_ATTACHMENT = :hasAttachment, " +
                "ATTACHMENT_ID = :attachmentId WHERE SECURE_MESSAGE_ID = :secureMessageId AND OPLOCK = :oplock";

            OracleQuery query = new OracleQuery();
            OracleCommand command = new OracleCommand(sql);
            query.Command = command;

            // the ordering of these is hokey because the OracleParameters collection needs to have the values bound in the order
            // they appear in the SQL statement. Trying to re-use the buildMessageCommand query... could just past here but ok for now

            OracleParameter oplockPlusOneParam = new OracleParameter("oplockPlusOne", OracleDbType.Decimal);
            oplockPlusOneParam.Value = Convert.ToDecimal(message.Oplock + 1);
            query.Command.Parameters.Add(oplockPlusOneParam);

            buildMessageCommand(query.Command, message);

            // add the id param - not in helper function above
            OracleParameter idParam = new OracleParameter("secureMessageId", OracleDbType.Decimal);
            idParam.Value = Convert.ToDecimal(message.Id);
            query.Command.Parameters.Add(idParam);

            OracleParameter oplockParam = new OracleParameter("oplock", OracleDbType.Decimal);
            oplockParam.Value = Convert.ToDecimal(message.Oplock);
            query.Command.Parameters.Add(oplockParam);

            return query;
        }

        #endregion

        #region Delete Message
        internal void deleteMessage(Int32 messageId)
        {
            deleteMessage(messageId, false);
        }

        internal void deleteMessage(Int32 messageId, bool inactivate)
        {
            OracleQuery request = buildDeleteMessageQuery(messageId, inactivate);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 rowsAffected = (Int32)_cxn.query(request, qry);

            if (rowsAffected != 1)
            {
                throw new MdoException("Expected one record to be deleted. Rows affected: " + rowsAffected.ToString());
            }
        }

        internal OracleQuery buildDeleteMessageQuery(Int32 messageId, bool inactivate)
        {
            string sql = "DELETE FROM SMS.SECURE_MESSAGE WHERE SECURE_MESSAGE_ID=:secureMessageId";

            if (inactivate)
            {
                // TBD - which is the correct way? delete the record or set to inactive?
                sql = "UPDATE SMS.SECURE_MESSAGE SET ACTIVE=0 WHERE SECURE_MESSAGE_ID=:secureMessageId";
            }

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter msgIdParam = new OracleParameter("secureMessageId", OracleDbType.Decimal);
            msgIdParam.Value = Convert.ToDecimal(messageId);
            query.Command.Parameters.Add(msgIdParam);

            return query;
        }
        #endregion

        #region Get Message
        public domain.sm.Message getMessage(Int32 messageId)
        {
            OracleQuery request = buildGetMessageQuery(messageId);
            reader requestRdr = delegate() { return request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, requestRdr);
            return toMessage(response);
        }

        internal OracleQuery buildGetMessageQuery(int messageId)
        {
            string sql = "SELECT SECURE_MESSAGE_ID, CLINICIAN_STATUS, COMPLETED_DATE, ASSIGNED_TO, CHECKSUM, THREAD_ID, STATUS_SET_BY, " + 
                "OPLOCK AS SMOPLOCK, ESCALATED, SENT_DATE, SENDER_TYPE, SENDER_ID, SENDER_NAME, RECIPIENT_TYPE, RECIPIENT_ID, RECIPIENT_NAME, " + 
                "SENT_DATE_LOCAL, ESCALATION_NOTIFICATION_DATE, ESCALATION_NOTIFICATION_TRIES, READ_RECEIPT, HAS_ATTACHMENT, ATTACHMENT_ID " + 
                "FROM SMS.SECURE_MESSAGE WHERE SECURE_MESSAGE_ID=:messageId AND ACTIVE=1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter messageIdParam = new OracleParameter("messageId", OracleDbType.Decimal);
            messageIdParam.Value = Convert.ToDecimal(messageId);
            query.Command.Parameters.Add(messageIdParam);

            return query;
        }

        internal domain.sm.Message toMessage(IDataReader rdr)
        {
            domain.sm.Message message = new domain.sm.Message();

            if (rdr.Read())
            {
                message = Message.getMessageFromReader(rdr);
            }
            return message;
        }
        #endregion
        #endregion

        #region Reply
        public Message sendReply(domain.sm.Message original, domain.sm.Message reply)
        {
            reply.SentDate = new SystemDao(_cxn).getSystemTime(); // DateTime.Now;
            prepareReply(original, reply);
            try
            {
                _cxn.beginTransaction();
                
                Message dbReply = createMessage(reply);
                new AddresseeDao(_cxn).createAddressees(dbReply.Addressees, dbReply.Id);

                new MessageActivityDao(_cxn).createMessageActivity(
                    new MessageActivity()
                    {
                        Action = domain.sm.enums.ActivityEnum.MDWS_MESSAGE_SENT,
                        Detail = SmUtils.buildDetailString(reply),
                        MessageId = dbReply.Id,
                        PerformerType = domain.sm.enums.UserTypeEnum.PATIENT,
                        UserId = original.SenderId
                    });

                _cxn.commitTransaction();

                //new ThreadedEmailer(_cxn.DataSource.ConnectionString, Convert.ToInt32(original.SenderId), dbReply.Addressees.GetRange(1, dbReply.Addressees.Count - 1)).emailAllAsync();
                new ThreadedEmailer(_cxn.DataSource.ConnectionString, Convert.ToInt32(original.SenderId), dbReply.Addressees.GetRange(1, dbReply.Addressees.Count - 1)).emailAllViaConfig();

                return dbReply;
            }
            catch (Exception)
            {
                _cxn.rollbackTransaction();
                throw;
            }
        }

        internal Message prepareProviderReply(Message original, Message reply, domain.sm.User sender)
        {
            if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.CLINICIAN && original.Status == domain.sm.enums.ClinicianStatusEnum.COMPLETE)
            {
                throw new MdoException("Message has already been completed");
            }
            throw new NotImplementedException("Currently only able to reply as a patient");
        }
        
        internal Message preparePatientReply(Message original, Message reply, domain.sm.User sender)
        {
            if (original.MessageThread == null || original.MessageThread.MailGroup == null || original.MessageThread.MailGroup.Id <= 0)
            {
                throw new MdoException("No message thread or triage group defined");
            }
            reply.SenderType = sender.ParticipantType;
            reply.SenderName = sender.getName();
            reply.RecipientId = original.MessageThread.MailGroup.Id;
            checkValidMessageRecipientPatient(reply); // One would *think* the above line would validate this but, just in case there was some previous data integrity issue, we should check anyways. The helper function also sets other parameters (group name, etc) so not a wasted call

            AddresseeDao addrDao = new AddresseeDao(_cxn);
            addrDao.addSenderToMessage(sender, reply);
            addrDao.addRecipientsToMessage(Convert.ToInt32(reply.RecipientId), reply);

            return reply;
        }

        internal Message prepareReply(Message original, Message reply)
        {
            if (original == null || original.Id <= 0 || reply == null || reply.SenderId <= 0 || String.IsNullOrEmpty(reply.Body))
            {
                throw new ArgumentException("Insufficient information to process request");
            }

            original = getMessageComplete(original.Id);
            if (original == null || original.Id <= 0 || original.MessageThread == null || original.MessageThread.Id <= 0)
            {
                throw new MdoException("No message for that original message ID");
            }

            reply.MessageThread = original.MessageThread;
            domain.sm.User sender = new UserDao(_cxn).getUserById(Convert.ToInt32(reply.SenderId));
            
            if (sender == null)
            {
                throw new MdoException("No user exists for that user ID");
            }

            if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.PATIENT)
            {
                reply = preparePatientReply(original, reply, sender);
            }
            else if (reply.SenderType == domain.sm.enums.ParticipantTypeEnum.CLINICIAN)
            {
                reply = prepareProviderReply(original, reply, sender);
            }
            else
            {
                throw new NotImplementedException("Currently not allowed to send as " + Enum.GetName(typeof(domain.sm.enums.ParticipantTypeEnum), reply.SenderType));
            }
            return reply;
        }
        #endregion

        #region Draft Messages    
        public void deleteDraft(Message message)
        {
            if (message == null || message.Id <= 0)
            {
                throw new MdoException("Invalid Message");
            }
            Message dbMessage = getMessageComplete(message.Id);
            if (dbMessage == null || dbMessage.Id <= 0)
            {
                throw new MdoException("No message found with that ID");
            }
            if (dbMessage.SentDate.Year > 1900)
            {
                throw new MdoException("This message is not a valid draft - already sent");
            }
            if (dbMessage.MessageThread == null || dbMessage.MessageThread.Id <= 0 || dbMessage.Addressees == null || 
                dbMessage.Addressees.Count != 1 || dbMessage.Addressees[0] == null || dbMessage.Addressees[0].Id <= 0)
            {
                throw new MdoException("Data integrity - message thread/addressee appears malformed in database");
            }
            if (dbMessage.Addressees[0].FolderId != (int)domain.sm.enums.SystemFolderEnum.Drafts)
            {
                throw new MdoException("You can only delete messages marked as DRAFT");
            }
            try
            {
                _cxn.beginTransaction();

                AddresseeDao addrDao = new AddresseeDao(_cxn);

                // turns out there might be multiple draft messages for a thread - they should all be deleted
                domain.sm.Thread msgThread = getMessagesFromThread(dbMessage.MessageThread.Id);
                foreach (Message msg in msgThread.Messages)
                {
                    IList<domain.sm.Addressee> allAddressees = addrDao.getAddresseesForMessage(msg.Id);
                    if (allAddressees != null && allAddressees.Count != 1)
                    {
                        throw new MdoException("Data integrity: Invalid draft. This draft message has more than one addressee.");
                    }

                    if (msg.SentDate.Year > 1900 || msg.CompletedDate.Year > 1900 ||
                        allAddressees[0].Owner.Id != dbMessage.Addressees[0].Owner.Id || allAddressees[0].FolderId != (Int32)domain.sm.enums.SystemFolderEnum.Drafts)
                    {
                        throw new MdoException("Data integrity: There appears to be multiple messages associated with the thread. The data is inconsistent between them.");
                    }
                    addrDao.deleteAddressee(allAddressees[0].Id);
                    deleteMessage(msg.Id);
                }

                // see if there were any attachments for this draft message
                if (dbMessage.Attachment)
                {
                    AttachmentDao attachDao = new AttachmentDao(_cxn);
                    attachDao.deleteAttachment(Convert.ToInt32(dbMessage.AttachmentId));
                }
                

                deleteThread(dbMessage.MessageThread.Id);

                _cxn.commitTransaction();
            }
            catch (Exception)
            {
                _cxn.rollbackTransaction();
                throw;
            }
        }

        public Message saveDraft(Message message)
        {
            if (message == null || String.IsNullOrEmpty(message.Body) || message.MessageThread == null || message.CompletedDate.Year > 1900)
            {
                throw new MdoException("Invalid message");
            }
            if (message.SenderId <= 0)
            {
                throw new MdoException("Invalid user ID");
            }
            //if (message.MessageThread != null && message.MessageThread.Id > 0)
            //{
            //    throw new MdoException("Can't save more than on draft message to a thread");
            //}
            domain.sm.User sender = new UserDao(_cxn).getUserById(Convert.ToInt32(message.SenderId));
            if (sender == null)
            {
                throw new MdoException("No user found with that ID");
            }

            // is draft new or should we update
            if (message.Id > 0)
            {
                // get message - see if it's ripe for updating
                Message dbMessage = getMessageComplete(message.Id);
                if (dbMessage == null || dbMessage.Id <= 0 || dbMessage.MessageThread == null || dbMessage.MessageThread.Id <= 0 || dbMessage.Addressees == null ||
                    dbMessage.Addressees.Count != 1)
                {
                    throw new MdoException("Invalid message ID");
                }
                if (dbMessage.SenderId != message.SenderId || dbMessage.Addressees[0].Owner.Id != message.SenderId)
                {
                    throw new MdoException("Can't edit another user's messages");
                }
                if (dbMessage.SentDate.Year > 1900 || dbMessage.CompletedDate.Year > 1900)
                {
                    throw new MdoException("This message has already been sent - not a valid draft");
                }
                if (dbMessage.Addressees.Count > 1)
                {
                    throw new MdoException("Data integrity - this message has already been addressed to more than one user");
                }
                // need to copy over important fields before we assign dbMessage to message
                dbMessage.Body = message.Body;
                dbMessage.Checksum = gov.va.medora.utils.StringUtils.getMD5Hash(message.Body);
                dbMessage.Oplock = message.Oplock; // need to copy this over because it came from the client
                // if mail group changed
                if (message.MessageThread != null && message.MessageThread.MailGroup != null && message.MessageThread.MailGroup.Id > 0 && message.MessageThread.MailGroup.Id != dbMessage.MessageThread.MailGroup.Id)
                {
                    dbMessage.MessageThread.MailGroup = message.MessageThread.MailGroup;
                }
                // TODO - figure out copying over of message thread properties
                dbMessage.MessageThread.MessageCategoryType = message.MessageThread.MessageCategoryType;
                dbMessage.MessageThread.Subject = message.MessageThread.Subject;
                dbMessage.MessageThread.Oplock = message.MessageThread.Oplock;

                if (message.RecipientId > 0 && dbMessage.RecipientId != message.RecipientId)
                {
                    checkValidMessageRecipient(message, sender);
                    dbMessage.RecipientId = message.RecipientId;
                    dbMessage.RecipientName = message.RecipientName;
                    dbMessage.RecipientType = message.RecipientType;
                }
                message = dbMessage;

                try
                {
                    _cxn.beginTransaction();
                    updateThread(dbMessage.MessageThread);
                    message = updateMessage(dbMessage);
                    message.MessageThread = dbMessage.MessageThread;
                    _cxn.commitTransaction();
                }
                catch (Exception)
                {
                    _cxn.rollbackTransaction();
                    throw;
                }
            
            }
            else
            {
                if (message.MessageThread == null)
                {
                    throw new MdoException("No thread defined for new draft message");
                }

                message.SenderId = sender.Id;
                message.SenderName = sender.getName();
                message.SenderType = sender.ParticipantType;

                if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.PATIENT)
                {
                    checkValidMessageRecipientPatient(message);
                }
                else if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.CLINICIAN)
                {
                    checkValidMessageRecipientProvider(message);
                }
                try
                {
                    _cxn.beginTransaction();
                    domain.sm.Thread t = null;
                    if (message.MessageThread.Id <= 0)
                    {
                        t = createThread(message.MessageThread);
                    }
                    else
                    {
                        t = getThread(message.MessageThread.Id);
                        if (t == null || t.Id <= 0)
                        {
                            throw new MdoException("No thread found for that thread ID");
                        }
                    }
                    message = createMessage(message);
                    message.MessageThread = t;
                    AddresseeDao addrDao = new AddresseeDao(_cxn);
                    addrDao.addSenderToMessage(sender, message);
                    addrDao.createAddressees(message.Addressees, message.Id);
                    _cxn.commitTransaction();
                }
                catch (Exception)
                {
                    _cxn.rollbackTransaction();
                    throw;
                }
            }

            return message;
        }
        #endregion

        #region Draft -> Send
        public Message sendDraft(Message message)
        {
            if (message == null || message.Id <= 0)
            {
                throw new MdoException("Invalid message");
            }

            Message dbMessage = getMessageComplete(message.Id);
            if (dbMessage == null || dbMessage.Id <= 0)
            {
                throw new MdoException("No message found for that ID");
            }
            if (dbMessage.SentDate.Year > 1900)
            {
                throw new MdoException("This message has already been sent");
            }
            if (dbMessage.Addressees == null || dbMessage.Addressees.Count != 1)
            {
                throw new MdoException("Data integrity - message appears to be addressed to multiple recipients");
            }
            // set addressee properties for sender
            dbMessage.Addressees[0].Folder = new Folder() { Id = (Int32)domain.sm.enums.SystemFolderEnum.Sent, Name = Enum.GetName(typeof(domain.sm.enums.SystemFolderEnum), domain.sm.enums.SystemFolderEnum.Sent) };
            dbMessage.Addressees[0].FolderId = (Int32)domain.sm.enums.SystemFolderEnum.Sent;
            dbMessage.Addressees[0].Role = domain.sm.enums.AddresseeRoleEnum.SENDER;
            if (message.Addressees != null && message.Addressees[0] != null)
            {
                dbMessage.Addressees[0].Oplock = message.Addressees[0].Oplock;
            }

            dbMessage.SentDate = new SystemDao(_cxn).getSystemTime(); // DateTime.Now;
            dbMessage.Oplock = message.Oplock; // need to copy this over because it came from the client
            // this function should really just update a DRAFT - not also try and update the message first
            //dbMessage.Body = message.Body;
            //dbMessage.Checksum = gov.va.medora.utils.StringUtils.getMD5Hash(message.Body);
            //if (message.MessageThread != null && message.MessageThread.MailGroup != null && message.MessageThread.MailGroup.Id > 0 && message.MessageThread.MailGroup.Id != dbMessage.MessageThread.MailGroup.Id)
            //{
            //    if (message.MessageThread.MessageCategoryType != dbMessage.MessageThread.MessageCategoryType)
            //    {
            //        dbMessage.MessageThread.MessageCategoryType = message.MessageThread.MessageCategoryType;
            //    }
            //    if (!String.IsNullOrEmpty(message.MessageThread.Subject) && !String.Equals(dbMessage.MessageThread.Subject, message.MessageThread.Subject))
            //    {
            //        dbMessage.MessageThread.Subject = message.MessageThread.Subject;
            //    }
            //    if (message.MessageThread.MailGroup != null && message.MessageThread.MailGroup.Id > 0 && dbMessage.MessageThread.MailGroup.Id != message.MessageThread.MailGroup.Id)
            //    {
            //        dbMessage.MessageThread.MailGroup.Id = message.MessageThread.MailGroup.Id;
            //    }
            //    dbMessage.MessageThread.Oplock = message.MessageThread.Oplock;
            //}

            message = prepareMessage(dbMessage);
            message.Addressees.RemoveAt(1); // the prepare message function adds the sender again - we already did that with the call to getMessageComplete. Kinda hokey...

            try
            {
                _cxn.beginTransaction();
                // since not trying to update message, nothing should change in thread
                //dbMessage.MessageThread = updateThread(dbMessage.MessageThread);
                message = updateMessage(dbMessage);
                AddresseeDao addrDao = new AddresseeDao(_cxn);
                addrDao.updateAddressee(dbMessage.Addressees[0]); // update folder for sender
                addrDao.createAddressees(message.Addressees.GetRange(1, message.Addressees.Count - 1), message.Id);
                
                new MessageActivityDao(_cxn).createMessageActivity(
                    new MessageActivity()
                    {
                        Action = domain.sm.enums.ActivityEnum.MDWS_MESSAGE_SENT,
                        Detail = SmUtils.buildDetailString(message),
                        MessageId = message.Id,
                        PerformerType = domain.sm.enums.UserTypeEnum.PATIENT,
                        UserId = message.SenderId
                    });
                
                _cxn.commitTransaction();

                new ThreadedEmailer(_cxn.DataSource.ConnectionString, Convert.ToInt32(message.SenderId), message.Addressees.GetRange(1, message.Addressees.Count - 1)).emailAllViaConfig();
                //new ThreadedEmailer(_cxn.DataSource.ConnectionString, Convert.ToInt32(message.SenderId), message.Addressees.GetRange(1, message.Addressees.Count - 1)).emailAllAsync();

            }
            catch (Exception)
            {
                _cxn.rollbackTransaction();
                throw;
            }
            return message;
        }
        #endregion

        #region Message Helpers
        internal Message prepareCompletedProviderMessage(domain.sm.Message message)
        {
            // TODO - need to implement provider stuff...
            return message;

            if (message.RecipientType == domain.sm.enums.ParticipantTypeEnum.PATIENT || message.RecipientType == domain.sm.enums.ParticipantTypeEnum.CLINICIAN)
            {
                domain.sm.User recipient = new UserDao(_cxn).getUserById(Convert.ToInt32(message.RecipientId));
                if (recipient == null)
                {
                    throw new MdoException("No user for that recipient ID");
                }
                message.RecipientName = recipient.getName();
                message.RecipientType = recipient.ParticipantType;
            }
            else if (message.RecipientType == domain.sm.enums.ParticipantTypeEnum.DISTRIBUTION_GROUP)
            {
                // TODO implement some code to set the correct recipient values
            }
            else if (message.RecipientType == domain.sm.enums.ParticipantTypeEnum.CLINCIAN_TRIAGE)
            {
                // TODO implement some code to set the correct recipient values
            }

        }

        internal void checkValidMessageRecipient(domain.sm.Message message)
        {
            if (message == null || message.SenderId <= 0)
            {
                throw new MdoException("No sender for message");
            }
            domain.sm.User sender = new UserDao(_cxn).getUserById(Convert.ToInt32(message.SenderId));
            checkValidMessageRecipient(message, sender);
        }

        internal void checkValidMessageRecipient(domain.sm.Message message, domain.sm.User sender)
        {
            if (message == null || message.SenderId <= 0 || sender == null || sender.Id <= 0)
            {
                throw new MdoException("No valid sender for message");
            }
            if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.PATIENT)
            {
                checkValidMessageRecipientPatient(message);
            }
            else if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.CLINICIAN)
            {
                checkValidMessageRecipientProvider(message);
            }
            else
            {
                throw new NotImplementedException("Unable to verify recipients for sender type: " + Enum.GetName(typeof(domain.sm.enums.ParticipantTypeEnum), sender.ParticipantType));
            }
        }

        internal void checkValidMessageRecipientProvider(domain.sm.Message message)
        {
            throw new NotImplementedException("Not currently handling provider messages");
        }

        internal void checkValidMessageRecipientPatient(domain.sm.Message message)
        {
            IList<TriageGroup> validGroups = new UserDao(_cxn).getValidRecipientsForPatient(Convert.ToInt32(message.SenderId)); // should be the user ID - senderId and recipient don't change

            if (validGroups == null || validGroups.Count == 0)
            {
                throw new MdoException("The user is not assigned any recipient groups");
            }

            TriageGroup validGroup = null;
            foreach (TriageGroup group in validGroups)
            {
                if (group.Id == Convert.ToInt32(message.RecipientId))
                {
                    validGroup = group;
                    break;
                }
            }

            // if we didn't find a valid group using the recipient ID, we can see if the message thread's mail group property contains that data
            if (validGroup == null)
            {
                if (message.MessageThread != null && message.MessageThread.MailGroup != null && message.MessageThread.MailGroup.Id > 0)
                {
                    foreach (TriageGroup group in validGroups)
                    {
                        if (group.Id == message.MessageThread.MailGroup.Id)
                        {
                            message.RecipientId = message.MessageThread.MailGroup.Id; // correct recipient to use the mail group ID 
                            validGroup = message.MessageThread.MailGroup;
                            break;
                        }
                    }
                }
            }
            // if we still don't have a valid group then throw an error
            if (validGroup == null)
            {
                throw new MdoException("The patient does not have permission to send secure messages to that group");
            }

            message.MessageThread.MailGroup = new TriageGroup() { Id = validGroup.Id, Name = validGroup.Name, Oplock = validGroup.Oplock }; 
            message.RecipientName = validGroup.Name;
            message.RecipientType = domain.sm.enums.ParticipantTypeEnum.CLINCIAN_TRIAGE;
        }

        internal Message prepareIncompletePatientMessage(domain.sm.Message message)
        {
            if (message.RecipientId > 0)
            {
                checkValidMessageRecipientPatient(message);
            }
            
            return message;
        }

        internal Message prepareCompletedPatientMessage(domain.sm.Message message)
        {
            message.SentDate = new SystemDao(_cxn).getSystemTime(); // DateTime.Now; // setting completed date marks message as done (i.e. not a draft)

            if (message.RecipientId <= 0)
            {
                throw new MdoException("Must specify a recipient for messages marked as complete");
            }

            checkValidMessageRecipientPatient(message); // helper also sets message.Recipient* properties to correct values based on database

            new AddresseeDao(_cxn).addRecipientsToMessage(Convert.ToInt32(message.RecipientId), message);

            return message;
        }

        /// <summary>
        /// Prepare a message for sending. Creates thread if needed. Assigns addressees. Verifies message recipient
        /// </summary>
        /// <param name="message"></param>
        /// <returns></returns>
        internal Message prepareMessage(Message message)
        {
            if (message.MessageThread == null)
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "No thread defined");
            }
            if (message.MessageThread.Id <= 0 && String.IsNullOrEmpty(message.MessageThread.Subject))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "No message ID or message subject specified");
            }
            if (message.SenderId <= 0)
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL_PATIENT_ID, "No user associated with message");
            }

            if (message.MessageThread.Id <= 0)
            {
                message.MessageThread = createThread(message.MessageThread); // the message.MessageThread should have the fields needed to create
            }

            domain.sm.User sender = new UserDao(_cxn).getUserById(Convert.ToInt32(message.SenderId));
            new AddresseeDao(_cxn).addSenderToMessage(sender, message);

            message.SenderName = sender.getName();
            message.SenderType = sender.ParticipantType;

            if (message.SentDate.Year > 1900) // if message is ready to be sent
            {
                if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.PATIENT) // if user is a patient, make sure recipient is ok
                {
                    return prepareCompletedPatientMessage(message);
                }
                else if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.CLINICIAN)
                {
                    return prepareCompletedProviderMessage(message);
                }
            }
            else if (message.SentDate.Year < 1900)
            {
                if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.PATIENT)
                {
                    return prepareIncompletePatientMessage(message);
                }
                else if (sender.ParticipantType == domain.sm.enums.ParticipantTypeEnum.CLINICIAN)
                {
                    // TODO - implement some code to take care of incomplete messages
                }
            }

            return message;
        }

        #endregion

        #region Send New Message

        /// <summary>
        /// Create and send new message. Creates thread if needed. Populates addressees and sends email notifications where applicable
        /// </summary>
        /// <param name="message"></param>
        /// <returns></returns>
        public Message sendNewMessage(Message message)
        {
            return sendMessage(message, true);
        }

        /// <summary>
        /// The primary function for writing a message. Writes message and addressees to the database. Verifies recipients and creates thread if 'verify' is set. Also email recipients 
        /// if Message.SentDate has been set. The first Message.Addressees item should be the sender for correct email notifications. Uses a SQL transaction
        /// </summary>
        /// <param name="message"></param>
        /// <returns></returns>
        internal Message sendMessage(Message message, bool verify)
        {
            try
            {
                _cxn.beginTransaction();

                if (verify)
                {
                    prepareMessage(message);
                }
                message = createMessage(message);
                new AddresseeDao(_cxn).createAddressees(message.Addressees, message.Id);

                MessageActivity activity = new MessageActivityDao(_cxn).createMessageActivity(
                    new MessageActivity()
                    {
                        Action = domain.sm.enums.ActivityEnum.MDWS_MESSAGE_SENT,
                        Detail = SmUtils.buildDetailString(message),
                        MessageId = message.Id,
                        PerformerType = domain.sm.enums.UserTypeEnum.PATIENT,
                        UserId = message.SenderId
                    });


                _cxn.commitTransaction();

                if (message.SentDate.Year > 1900 && message.Addressees.Count > 1) // if message was sent, send email notifications to recipients
                {
                    //new ThreadedEmailer(_cxn.DataSource.ConnectionString, Convert.ToInt32(activity.UserId), message.Addressees.GetRange(1, message.Addressees.Count - 1)).emailAllAsync();
                    new ThreadedEmailer(_cxn.DataSource.ConnectionString, Convert.ToInt32(activity.UserId), message.Addressees.GetRange(1, message.Addressees.Count - 1)).emailAllViaConfig();
                }

                return message;
            }
            catch (Exception)
            {
                _cxn.rollbackTransaction();
                throw;
            }
        }

        internal OracleQuery buildSendMessageCommand(Message message)
        {
            // SECURE_MESSAGE table messages with SENT_DATE = DBNULL are drafts
            string sql = "INSERT INTO SMS.SECURE_MESSAGE (CLINICIAN_STATUS, COMPLETED_DATE, " +
                "ASSIGNED_TO, CHECKSUM, THREAD_ID, STATUS_SET_BY, MODIFIED_DATE, " +
                "ESCALATED, BODY, SENT_DATE, SENDER_TYPE, SENDER_ID, SENDER_NAME, RECIPIENT_TYPE, " +
                "RECIPIENT_ID, RECIPIENT_NAME, SENT_DATE_LOCAL, ESCALATION_NOTIFICATION_DATE, " +
                "ESCALATION_NOTIFICATION_TRIES, READ_RECEIPT, HAS_ATTACHMENT, ATTACHMENT_ID) VALUES (" +
                ":clinicianStatus, :completedDate, :assignedTo, :checksum, :threadId, :statusSetBy, " +
                ":modifiedDate, :escalated, :body, :sentDate, :senderType, :senderId, " +
                ":senderName, :recipientType, :recipientId, :recipientName, :sentDateLocal, " +
                ":escalationNotificationDate, :escalationNotificationTries, :readReceipt, :hasAttachment, :attachmentId) " +
                "RETURNING SECURE_MESSAGE_ID INTO :outId";

            OracleQuery query = new OracleQuery();
            OracleCommand command = new OracleCommand(sql);
            query.Command = command;

            buildMessageCommand(query.Command, message);

            // add out ID - not in helper function
            OracleParameter outParam = new OracleParameter("outId", OracleDbType.Decimal);
            outParam.Direction = ParameterDirection.Output;
            command.Parameters.Add(outParam);

            return query;
        }

        internal OracleCommand buildMessageCommand(OracleCommand command, Message message)
        {
            OracleParameter clinicianStatusParam = new OracleParameter("clinicianStatus", OracleDbType.Decimal);
            clinicianStatusParam.Value = message.Status;
            command.Parameters.Add(clinicianStatusParam);

            OracleParameter completedDateParam = new OracleParameter("completedDate", OracleDbType.Date);
            if (message.CompletedDate != null && message.CompletedDate.Year > 1900)
            {
                completedDateParam.Value = new Oracle.DataAccess.Types.OracleDate(message.CompletedDate);
            }
            else
            {
                completedDateParam.Value = DBNull.Value;
            }
            command.Parameters.Add(completedDateParam);

            OracleParameter assignedToParam = new OracleParameter("assignedTo", OracleDbType.Decimal);
            if (message.AssignedTo != null && message.AssignedTo.Id != 0)
            {
                assignedToParam.Value = Convert.ToDecimal(message.AssignedTo.Id);
            }
            else
            {
                assignedToParam.Value = DBNull.Value;
            }
            command.Parameters.Add(assignedToParam);

            OracleParameter checksumParam = new OracleParameter("checksum", OracleDbType.Char, 32);
            if (!String.IsNullOrEmpty(message.Checksum))
            {
                checksumParam.Value = message.Checksum;
            }
            else if (String.IsNullOrEmpty(message.Checksum) && !String.IsNullOrEmpty(message.Body))
            {
                checksumParam.Value = gov.va.medora.utils.StringUtils.getMD5Hash(message.Body);
            }
            else
            {
                checksumParam.Value = DBNull.Value;
            }
            command.Parameters.Add(checksumParam);

            OracleParameter threadIdParam = new OracleParameter("threadId", OracleDbType.Decimal);
            // no longer creating thread here...
            // must supply a message thread with a subject and no ID to create a new thread on the fly
            //if (message.MessageThread != null && message.MessageThread.Id == 0 && !String.IsNullOrEmpty(message.MessageThread.Subject)) 
            //{
            //    message.MessageThread = createThread(message.MessageThread); // create a new thread for this message
            //}
            if (message.MessageThread == null || message.MessageThread.Id <= 0)
            {
                throw new ArgumentNullException("Must supply a valid message thread for the secure message");
            }
            threadIdParam.Value = Convert.ToDecimal(message.MessageThread.Id);
            command.Parameters.Add(threadIdParam);

            OracleParameter statusSetByParam = new OracleParameter("statusSetBy", OracleDbType.Decimal);
            if (message.StatusSetBy != null && message.StatusSetBy.Id > 0)
            {
                statusSetByParam.Value = Convert.ToDecimal(message.StatusSetBy.Id);
            }
            else
            {
                statusSetByParam.Value = DBNull.Value;
            }
            command.Parameters.Add(statusSetByParam);

            // TODO - fix created date to use Message.CreatedDate property so we're not updating it every time. Ok now while testing...
            //OracleParameter createdDateParam = new OracleParameter("createdDate", OracleDbType.Date);
            //createdDateParam.Value = new Oracle.DataAccess.Types.OracleDate(DateTime.Now);
            //command.Parameters.Add(createdDateParam);

            //OracleParameter modifiedDateParam = new OracleParameter("modifiedDate", OracleDbType.Date);
            //modifiedDateParam.Value = new Oracle.DataAccess.Types.OracleDate(DateTime.Now);
            //command.Parameters.Add(modifiedDateParam);

            OracleParameter escalatedParam = new OracleParameter("escalated", OracleDbType.Date);
            if (message.EscalatedDate != null && message.EscalatedDate.Year > 1900)
            {
                escalatedParam.Value = new Oracle.DataAccess.Types.OracleDate(message.EscalatedDate);
            }
            else
            {
                escalatedParam.Value = DBNull.Value;
            }
            command.Parameters.Add(escalatedParam);

            OracleParameter bodyParam = new OracleParameter("body", OracleDbType.Clob);
            bodyParam.Value = message.Body;
            command.Parameters.Add(bodyParam);

            OracleParameter sentDateParam = new OracleParameter("sentDate", OracleDbType.Date);
            if (message.SentDate != null && message.SentDate.Year > 1900)
            {
                sentDateParam.Value = new Oracle.DataAccess.Types.OracleDate(message.SentDate);
            }
            else
            {
                sentDateParam.Value = DBNull.Value;
            }
            command.Parameters.Add(sentDateParam);

            OracleParameter senderTypeParam = new OracleParameter("senderType", OracleDbType.Decimal);
            senderTypeParam.Value = Convert.ToDecimal((Int32)message.SenderType);
            command.Parameters.Add(senderTypeParam);

            OracleParameter senderIdParam = new OracleParameter("senderId", OracleDbType.Decimal);
            senderIdParam.Value = Convert.ToDecimal(message.SenderId);
            command.Parameters.Add(senderIdParam);

            OracleParameter senderNameParam = new OracleParameter("senderName", OracleDbType.Varchar2, 100);
            senderNameParam.Value = message.SenderName;
            command.Parameters.Add(senderNameParam);

            OracleParameter recipientTypeParam = new OracleParameter("recipientType", OracleDbType.Decimal);
            if (message.RecipientType != null)
            {
                recipientTypeParam.Value = Convert.ToDecimal((Int32)message.RecipientType);
            }
            else
            {
                recipientTypeParam.Value = DBNull.Value;
            }
            command.Parameters.Add(recipientTypeParam);

            OracleParameter recipientIdParam = new OracleParameter("recipientId", OracleDbType.Decimal);
            if (message.RecipientId > 0)
            {
                recipientIdParam.Value = Convert.ToDecimal(message.RecipientId);
            }
            else
            {
                recipientIdParam.Value = DBNull.Value;
            }
            command.Parameters.Add(recipientIdParam);

            OracleParameter recipientNameParam = new OracleParameter("recipientName", OracleDbType.Varchar2, 100);
            if (!String.IsNullOrEmpty(message.RecipientName))
            {
                recipientNameParam.Value = message.RecipientName;
            }
            else
            {
                recipientNameParam.Value = DBNull.Value;
            }
            command.Parameters.Add(recipientNameParam);

            OracleParameter sentDateLocalParam = new OracleParameter("sentDateLocal", OracleDbType.Date);
            sentDateLocalParam.Value = sentDateParam.Value;
            command.Parameters.Add(sentDateLocalParam);

            OracleParameter escalationNotificationDateParam = new OracleParameter("escalationNotificationDate", OracleDbType.Date);
            if (message.EscalationNotificationDate != null && message.EscalationNotificationDate.Year > 1900)
            {
                escalationNotificationDateParam.Value = new Oracle.DataAccess.Types.OracleDate(message.EscalationNotificationDate);
            }
            else
            {
                escalationNotificationDateParam.Value = DBNull.Value;
            }
            command.Parameters.Add(escalationNotificationDateParam);

            OracleParameter escalationNotificationTriesParam = new OracleParameter("escalationNotificationTries", OracleDbType.Decimal);
            if (message.EscalationNotificationTries > 0)
            {
                escalationNotificationTriesParam.Value = Convert.ToDecimal(message.EscalationNotificationTries);
            }
            else
            {
                escalationNotificationTriesParam.Value = DBNull.Value;
            }
            command.Parameters.Add(escalationNotificationTriesParam);

            OracleParameter readReceiptParam = new OracleParameter("readReceipt", OracleDbType.Varchar2, 20);
            if (!String.IsNullOrEmpty(message.ReadReceipt))
            {
                readReceiptParam.Value = message.ReadReceipt;
            }
            else
            {
                readReceiptParam.Value = DBNull.Value;
            }
            command.Parameters.Add(readReceiptParam);

            OracleParameter hasAttachmentParam = new OracleParameter("hasAttachment", OracleDbType.Decimal);
            hasAttachmentParam.Value = Convert.ToDecimal(message.Attachment ? 1 : 0);
            command.Parameters.Add(hasAttachmentParam);

            OracleParameter attachmentIdParam = new OracleParameter("attachmentId", OracleDbType.Decimal);
            if (message.Attachment && message.AttachmentId > 0)
            {
                attachmentIdParam.Value = Convert.ToDecimal(message.AttachmentId);
            }
            else
            {
                attachmentIdParam.Value = DBNull.Value;
            }
            command.Parameters.Add(attachmentIdParam);

            return command;
        }
        #endregion


    }
}
