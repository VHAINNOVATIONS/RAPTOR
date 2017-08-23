using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Oracle.DataAccess;
using Oracle.DataAccess.Client;
using gov.va.medora.mdo.domain.sm;
using Oracle.DataAccess.Types;
using System.Data;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.oracle.mhv.sm
{
    public class AddresseeDao
    {
        MdoOracleConnection _cxn;
        delegate OracleDataReader reader();
        delegate Int32 nonQuery();

        public AddresseeDao(AbstractConnection cxn)
        {
            _cxn = (MdoOracleConnection)cxn;
        }

        #region Addressee CRUD
        #region Delete Addressee
        internal void deleteAddressee(Int32 addresseeId)
        {
            OracleQuery request = buildDeleteAddresseeQuery(addresseeId);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 rowsAffected = (Int32)_cxn.query(request, qry);

            if (rowsAffected != 1)
            {
                throw new MdoException("Unable to delete addressee");
            }
        }

        internal OracleQuery buildDeleteAddresseeQuery(Int32 addresseeId)
        {
            string sql = "DELETE FROM SMS.ADDRESSEE WHERE ADDRESSEE_ID=:addresseeId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter addresseeIdParam = new OracleParameter("addresseeId", OracleDbType.Decimal);
            addresseeIdParam.Value = Convert.ToDecimal(addresseeId);
            query.Command.Parameters.Add(addresseeIdParam);

            return query;
        }
        #endregion

        #region Update Addressee
        /// <summary>
        /// This method should not be used to update the read date or reminder date unless the appropriate date offset was applied to those fields
        /// </summary>
        /// <param name="addressee"></param>
        /// <returns></returns>
        public Addressee updateAddressee(Addressee addressee)
        {
            OracleQuery request = buildUpdateAddresseeQuery(addressee);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 rowsAffected = (Int32)_cxn.query(request, qry);

            if (rowsAffected != 1)
            {
                throw new MdoException("Unable to update addressee");
            }

            addressee.Oplock++;
            return addressee;
        }

        internal OracleQuery buildUpdateAddresseeQuery(Addressee addressee)
        {
            string sql = "UPDATE SMS.ADDRESSEE SET OPLOCK=:oplockPlusOne, MODIFIED_DATE=SYSDATE, FOLDER_ID=:folderId, READ_DATE=:readDate, " +
                "REMINDER_DATE=:reminderDate WHERE ADDRESSEE_ID=:addresseeId AND OPLOCK=:oplock";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter oplockPlusOneParam = new OracleParameter("oplockPlusOne", OracleDbType.Decimal);
            oplockPlusOneParam.Value = Convert.ToDecimal(addressee.Oplock + 1);
            query.Command.Parameters.Add(oplockPlusOneParam);

            //OracleParameter modifiedDateParam = new OracleParameter("modifiedDate", OracleDbType.Date);
            //modifiedDateParam.Value = new OracleDate(DateTime.Now);
            //query.Command.Parameters.Add(modifiedDateParam);

            OracleParameter folderIdParam = new OracleParameter("folderId", OracleDbType.Decimal);
            folderIdParam.Value = Convert.ToDecimal(addressee.FolderId);
            query.Command.Parameters.Add(folderIdParam);

            OracleParameter readDateParam = new OracleParameter("readDate", OracleDbType.Date);
            if (addressee.ReadDate.Year > 1900)
            {
                readDateParam.Value = new OracleDate(addressee.ReadDate);
            }
            else
            {
                readDateParam.Value = DBNull.Value;
            }
            query.Command.Parameters.Add(readDateParam);

            OracleParameter reminderDateParam = new OracleParameter("reminderDate", OracleDbType.Date);
            if (addressee.ReminderDate.Year > 1900)
            {
                reminderDateParam.Value = new OracleDate(addressee.ReminderDate);
            }
            else
            {
                reminderDateParam.Value = DBNull.Value;
            }
            query.Command.Parameters.Add(reminderDateParam);

            OracleParameter addresseeIdParam = new OracleParameter("addresseeId", OracleDbType.Decimal);
            addresseeIdParam.Value = Convert.ToDecimal(addressee.Id);
            query.Command.Parameters.Add(addresseeIdParam);

            OracleParameter oplockParam = new OracleParameter("oplock", OracleDbType.Decimal);
            oplockParam.Value = Convert.ToDecimal(addressee.Oplock);
            query.Command.Parameters.Add(oplockParam);

            return query;
        }
        #endregion

        #region Create Addressee
        internal Addressee createAddressee(Addressee addressee, Int32 messageId)
        {
            OracleQuery query = buildCreateAddresseeQuery(addressee, messageId);
            nonQuery insertQuery = delegate() { return query.Command.ExecuteNonQuery(); };
            _cxn.query(query, insertQuery);
            addressee.Id = ((Oracle.DataAccess.Types.OracleDecimal)query.Command.Parameters["outId"].Value).ToInt32();
            return addressee;
        }

        internal void createAddressees(IList<domain.sm.Addressee> addressees, Int32 messageId)
        {
            foreach (Addressee addr in addressees)
            {
                addr.Id = createAddressee(addr, messageId).Id;
                //OracleQuery query = buildCreateAddresseeQuery(addr, messageId);
                //nonQuery insertQuery = delegate() { return query.Command.ExecuteNonQuery(); };
                //_cxn.query(query, insertQuery);
            }
        }

        internal OracleQuery buildCreateAddresseeQuery(domain.sm.Addressee addressee, Int32 messageId)
        {
            string sql = "INSERT INTO SMS.ADDRESSEE (ADDRESSEE_ROLE, SECURE_MESSAGE_ID, USER_ID, FOLDER_ID) VALUES (:addresseeRole, :smId, :userId, :folderId) "+
                "RETURNING ADDRESSEE_ID INTO :outId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter addresseeRoleParam = new OracleParameter("addresseeRole", OracleDbType.Decimal);
            addresseeRoleParam.Value = Convert.ToDecimal((Int32)addressee.Role);
            query.Command.Parameters.Add(addresseeRoleParam);

            OracleParameter smIdParam = new OracleParameter("smId", OracleDbType.Decimal);
            smIdParam.Value = Convert.ToDecimal(messageId);
            query.Command.Parameters.Add(smIdParam);

            OracleParameter userIdParam = new OracleParameter("userId", OracleDbType.Decimal);
            userIdParam.Value = Convert.ToDecimal(addressee.Owner.Id);
            query.Command.Parameters.Add(userIdParam);

            OracleParameter folderIdParam = new OracleParameter("folderId", OracleDbType.Decimal);
            folderIdParam.Value = Convert.ToDecimal(addressee.Folder.Id);
            query.Command.Parameters.Add(folderIdParam);

            OracleParameter outParam = new OracleParameter("outId", OracleDbType.Decimal);
            outParam.Direction = ParameterDirection.Output;
            query.Command.Parameters.Add(outParam);

            return query;
        }
        #endregion

        #region Get Addressee
        public Addressee getAddressee(Int32 addresseeId)
        {
            OracleQuery request = buildGetAddresseeQuery(addresseeId);
            reader requestRdr = delegate() { return request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, requestRdr);
            return toAddressee(response);
        }

        internal OracleQuery buildGetAddresseeQuery(int addresseeId)
        {
            string sql = "SELECT ADDRESSEE_ID, ADDRESSEE_ROLE, SECURE_MESSAGE_ID, USER_ID, OPLOCK AS ADDROPLOCK, FOLDER_ID, READ_DATE, REMINDER_DATE " +
                "FROM SMS.ADDRESSEE WHERE ADDRESSEE_ID=:addresseeId AND ACTIVE=1"; 

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter addresseeIdParam = new OracleParameter("addresseeId", OracleDbType.Decimal);
            addresseeIdParam.Value = Convert.ToDecimal(addresseeId);
            query.Command.Parameters.Add(addresseeIdParam);

            return query;
        }

        internal Addressee toAddressee(IDataReader rdr)
        {
            Addressee addressee = new Addressee();

            if (rdr.Read())
            {
                addressee = Addressee.getAddresseeFromReader(rdr);
            }
            return addressee;
        }

        #endregion
        #endregion

        public IList<Addressee> getAddresseesForMessage(Int32 messageId)
        {
            OracleQuery request = buildGetAddresseesForMessageQuery(messageId);
            reader requestRdr = delegate() { return request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, requestRdr);
            return toAddressees(response);
        }

        internal OracleQuery buildGetAddresseesForMessageQuery(int messageId)
        {
            string sql = "SELECT ADDRESSEE_ID, ADDRESSEE_ROLE, SECURE_MESSAGE_ID, USER_ID, OPLOCK AS ADDROPLOCK, FOLDER_ID, READ_DATE, REMINDER_DATE " + 
                "FROM SMS.ADDRESSEE WHERE SECURE_MESSAGE_ID=:messageId AND ACTIVE=1"; 

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter messageIdParam = new OracleParameter("messageId", OracleDbType.Decimal);
            messageIdParam.Value = messageId;
            query.Command.Parameters.Add(messageIdParam);

            return query;
        }

        public IList<Addressee> toAddressees(IDataReader rdr)
        {
            IList<Addressee> addressees = new List<Addressee>();
            Dictionary<string, bool> addresseesSchema = QueryUtils.getColumnExistsTable(TableSchemas.ADDRESSEE_COLUMNS, rdr);

            while (rdr.Read())
            {
                addressees.Add(Addressee.getAddresseeFromReader(rdr, addresseesSchema));
            }

            return addressees;
        }

        /// <remarks>
        /// This function retrieves the message ID from the row that is updated. It does this so as not to put the burden
        /// on the consuming client application to pass the message ID value. This should make it less confusing which identifier 
        /// is which and therefore the service easier to consume
        /// </remarks>
        /// <summary>
        /// Mark a message as read in the Addressee table. Set the ReadDate property to current timestamp toggle the date on. Or,
        /// set the ReadDate property to a new DateTime() - year of 1 - to toggle the read date off
        /// </summary>
        /// <param name="addressee"></param>
        /// <returns></returns>
        public Addressee readMessage(Addressee addressee)
        {
            _cxn.beginTransaction();

            try
            {
                Addressee original = getAddressee(addressee.Id);

                OracleQuery query = buildReadMessageRequest(addressee);
                nonQuery insertQuery = delegate() { return query.Command.ExecuteNonQuery(); };
                if ((Int32)_cxn.query(query, insertQuery) != 1)
                {
                    throw new mdo.exceptions.MdoException("Unable to mark message as read");
                }

                Int32 msgId = ((Oracle.DataAccess.Types.OracleDecimal)query.Command.Parameters["outId"].Value).ToInt32();
                addressee.Oplock++;

                new MessageActivityDao(_cxn).createMessageActivity(
                    new MessageActivity()
                    {
                        Action = domain.sm.enums.ActivityEnum.MDWS_MESSAGE_READ,
                        Detail = "MOBILE_APPS_ENTRY^MessageRead",
                        MessageId = msgId,
                        PerformerType = domain.sm.enums.UserTypeEnum.PATIENT,
                        UserId = original.Owner.Id
                    });

                SecureMessageDao msgDao = new SecureMessageDao(_cxn);
                addressee.Message = msgDao.getSecureMessageBody(msgId);

                _cxn.commitTransaction();

                // TBD - any business rules around SECURE_MESSAGE.READ_RECEIPT and marking a message as read?
                return addressee;
            }
            catch (Exception)
            {
                _cxn.rollbackTransaction();
                throw;
            }
        }

        internal OracleQuery buildReadMessageRequest(Addressee addressee)
        {
            string sql = "UPDATE SMS.ADDRESSEE SET READ_DATE=SYSDATE, OPLOCK=:oplockPlusOne, MODIFIED_DATE=SYSDATE " + 
                "WHERE ADDRESSEE_ID=:addresseeId AND OPLOCK=:oplock RETURNING SECURE_MESSAGE_ID INTO :outId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            //OracleParameter readDateParam = new OracleParameter("readDate", OracleDbType.Date);
            //readDateParam.Value = new OracleDate(addressee.ReadDate = DateTime.Now);
            //query.Command.Parameters.Add(readDateParam);

            OracleParameter oplockPlusOneParam = new OracleParameter("oplockPlusOne", OracleDbType.Decimal);
            oplockPlusOneParam.Value = Convert.ToDecimal(addressee.Oplock + 1);
            query.Command.Parameters.Add(oplockPlusOneParam);

            //OracleParameter modifiedDateParam = new OracleParameter("modifiedDate", OracleDbType.Date);
            //modifiedDateParam.Value = new OracleDate(DateTime.Now);
            //query.Command.Parameters.Add(modifiedDateParam);

            OracleParameter addresseeIdParam = new OracleParameter("addresseeId", OracleDbType.Decimal);
            addresseeIdParam.Value = Convert.ToDecimal(addressee.Id);
            query.Command.Parameters.Add(addresseeIdParam);

            OracleParameter oplockParam = new OracleParameter("oplock", OracleDbType.Decimal);
            oplockParam.Value = Convert.ToDecimal(addressee.Oplock);
            query.Command.Parameters.Add(oplockParam);

            OracleParameter outParam = new OracleParameter("outId", OracleDbType.Decimal);
            outParam.Direction = ParameterDirection.Output;
            query.Command.Parameters.Add(outParam);

            return query;
        }

        internal void addSenderToMessage(domain.sm.User sender, Message message)
        {
            if (message.Addressees == null)
            {
                message.Addressees = new List<Addressee>();
            }
            message.Addressees.Add(
                new Addressee()
                {
                    Message = message,
                    Owner = sender,
                    Role = domain.sm.enums.AddresseeRoleEnum.SENDER
                });

            if (message.SentDate.Year > 1900)
            {
                message.Addressees[message.Addressees.Count - 1].FolderId = (Int32)domain.sm.enums.SystemFolderEnum.Sent;
                message.Addressees[message.Addressees.Count - 1].Folder = new Folder() 
                { 
                    Id = (Int32)domain.sm.enums.SystemFolderEnum.Sent,
                    Name = Enum.GetName(typeof(domain.sm.enums.SystemFolderEnum), domain.sm.enums.SystemFolderEnum.Sent)
                };
            }
            else
            {
                message.Addressees[message.Addressees.Count - 1].FolderId = (Int32)domain.sm.enums.SystemFolderEnum.Drafts;
                message.Addressees[message.Addressees.Count - 1].Folder = new Folder() 
                { 
                    Id = (Int32)domain.sm.enums.SystemFolderEnum.Drafts,
                    Name = Enum.GetName(typeof(domain.sm.enums.SystemFolderEnum), domain.sm.enums.SystemFolderEnum.Drafts)
                };
            }
        }

        internal void addRecipientsToMessage(Int32 triageGroupId, Message message)
        {
            UserDao userDao = new UserDao(_cxn);
            IList<domain.sm.Clinician> groupMembers = userDao.getTriageGroupMembers(triageGroupId);
            if (message.Addressees == null)
            {
                message.Addressees = new List<Addressee>();
            }
            // with surrogacy, it appears there's a possibility of users appearing in the addressee list more than one time. so, we're going to check that and remove dupes
            Dictionary<String, Clinician> addresseeDict = new Dictionary<String, Clinician>(); // use to track addressees
            foreach (domain.sm.Clinician user in groupMembers)
            {
                if (!addresseeDict.ContainsKey(user.Id.ToString()))
                {
                    addresseeDict.Add(user.Id.ToString(), user);
                    message.Addressees.Add(
                        new Addressee()
                        {
                            Folder = new Folder() { Id = (Int32)domain.sm.enums.SystemFolderEnum.Inbox, Name = Enum.GetName(typeof(domain.sm.enums.SystemFolderEnum), domain.sm.enums.SystemFolderEnum.Inbox) },
                            FolderId = (Int32)domain.sm.enums.SystemFolderEnum.Inbox,
                            Message = message,
                            Owner = user,
                            Role = domain.sm.enums.AddresseeRoleEnum.RECIPIENT
                        });
                }

                IList<domain.sm.Clinician> surrogates = userDao.getUsersSurrogates(user.Id);
                foreach (Clinician surrogate in surrogates)
                {
                    if (!addresseeDict.ContainsKey(surrogate.Id.ToString()))
                    {
                        addresseeDict.Add(surrogate.Id.ToString(), user);
                        message.Addressees.Add(
                            new Addressee()
                            {
                                Folder = new Folder() { Id = (Int32)domain.sm.enums.SystemFolderEnum.Inbox, Name = Enum.GetName(typeof(domain.sm.enums.SystemFolderEnum), domain.sm.enums.SystemFolderEnum.Inbox) },
                                FolderId = (Int32)domain.sm.enums.SystemFolderEnum.Inbox,
                                Message = message,
                                Owner = surrogate,
                                Role = domain.sm.enums.AddresseeRoleEnum.RECIPIENT
                            });
                    }
                }
            }

        }

        internal Addressee getAddressee(int messageId, int userId)
        {
            OracleQuery request = buildGetAddresseeForMessageQuery(messageId, userId);
            reader requestRdr = delegate() { return request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, requestRdr);
            return toAddressee(response);
        }

        internal OracleQuery buildGetAddresseeForMessageQuery(int messageId, int userId)
        {
            string sql = "SELECT ADDRESSEE_ID, ADDRESSEE_ROLE, SECURE_MESSAGE_ID, USER_ID, OPLOCK AS ADDROPLOCK, FOLDER_ID, READ_DATE, REMINDER_DATE " +
                "FROM SMS.ADDRESSEE WHERE SECURE_MESSAGE_ID=:messageId AND USER_ID=:userId AND ACTIVE=1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter messageIdParam = new OracleParameter("messageId", OracleDbType.Decimal);
            messageIdParam.Value = messageId;
            query.Command.Parameters.Add(messageIdParam);

            OracleParameter userIdParam = new OracleParameter("userId", OracleDbType.Decimal);
            userIdParam.Value = userId;
            query.Command.Parameters.Add(userIdParam);

            return query;
        }

        #region Move Message
        public Addressee moveMessage(Message message, domain.sm.User user, Folder folder)
        {
            Addressee addressee = getAddressee(message.Id, user.Id);

            checkValidMove(addressee.Folder, folder);

            addressee.Folder = folder;
            addressee.FolderId = folder.Id;
            if (!addressee.Folder.SystemFolder)
            {
                FolderDao folderDao = new FolderDao(_cxn);
                addressee.Folder = folderDao.getUserFolder(user.Id, folder.Id);
            }
            return moveMessage(addressee);
        }

        internal Addressee moveMessage(Addressee addressee)
        {
            OracleQuery request = buildMoveMessageQuery(addressee);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 rowsAffected = (Int32)_cxn.query(request, qry);
            if (rowsAffected != 1)
            {
                throw new MdoException("Failed to move message");
            }
            addressee.Oplock++;
            return addressee;
        }

        internal OracleQuery buildMoveMessageQuery(Addressee addressee)
        {
            string sql = "UPDATE SMS.ADDRESSEE SET FOLDER_ID=:folderId, OPLOCK=:oplockPlusOne WHERE ADDRESSEE_ID=:addresseeId and OPLOCK=:oplock";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter folderIdParam = new OracleParameter("folderId", OracleDbType.Decimal);
            folderIdParam.Value = addressee.FolderId;
            query.Command.Parameters.Add(folderIdParam);

            OracleParameter oplockPlusOneParam = new OracleParameter("oplockPlusOne", OracleDbType.Decimal);
            oplockPlusOneParam.Value = addressee.Oplock + 1;
            query.Command.Parameters.Add(oplockPlusOneParam);

            OracleParameter addresseeParam = new OracleParameter("addresseeId", OracleDbType.Decimal);
            addresseeParam.Value = addressee.Id;
            query.Command.Parameters.Add(addresseeParam);

            OracleParameter oplockParam = new OracleParameter("oplock", OracleDbType.Decimal);
            oplockParam.Value = addressee.Oplock;
            query.Command.Parameters.Add(oplockParam);

            return query;
        }

        internal void checkValidMove(Folder oldFolder, Folder newFolder)
        {
            if (oldFolder.Id <= 0 && oldFolder.Id != (Int32)domain.sm.enums.SystemFolderEnum.Deleted && oldFolder.Id != (Int32)domain.sm.enums.SystemFolderEnum.Inbox)
            {
                throw new MdoException("Messages can only be moved out of user folders and inbox/deleted");
            }
            if (newFolder.Id <= 0 && newFolder.Id != (Int32)domain.sm.enums.SystemFolderEnum.Deleted && newFolder.Id != (Int32)domain.sm.enums.SystemFolderEnum.Inbox)
            {
                throw new MdoException("Messages can only be moved to user folders and inbox/deleted");
            }
        }
        #endregion

    }
}
