using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.domain.sm;
using gov.va.medora.mdo.dao.oracle.mhv.sm;
using gov.va.medora.mdo.dao.oracle;
using gov.va.medora.mdws.dto.sm;
using gov.va.medora.mdws.dto;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdws.conf;

namespace gov.va.medora.mdws
{
    public class SecureMessageLib
    {
        MySession _mySession;
        string _appPwd; // connection string

        public SecureMessageLib(MySession mySession)
        {
            _mySession = mySession;
        }

        public SecureMessageThreadsTO getMessages(string pwd, Int32 userId, Int32 folderId, Int32 pageStart, Int32 pageSize)
        {
            SecureMessageThreadsTO result = new SecureMessageThreadsTO(null);

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (userId <= 0)
            {
                result.fault = new dto.FaultTO("Must supply a user ID");
            }
            else if (pageSize <= 0 || pageSize > 250)
            {
                result.fault = new FaultTO("Invalid pageSize argument");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    SecureMessageDao dao = new SecureMessageDao(cxn);

                    if (folderId > 0 || Enum.IsDefined(typeof(gov.va.medora.mdo.domain.sm.enums.SystemFolderEnum), folderId))
                    {
                        return new SecureMessageThreadsTO(dao.getMessagesInFolder(userId, folderId, pageStart, pageSize));
                    }
                    else
                    {
                        return new SecureMessageThreadsTO(dao.getSecureMessages(userId, pageStart, pageSize));
                    }
                }
            }
            catch (Exception exc)
            {
                result.fault = new dto.FaultTO(exc);
            }

            return result;
        }

        private string getConnectionString(string pwd)
        {
            if (!String.IsNullOrEmpty(pwd))
            {
                return pwd;
            }
            else if (String.IsNullOrEmpty(pwd))
            {
                if (_mySession != null && _mySession.MdwsConfiguration != null && _mySession.MdwsConfiguration.AllConfigs != null &&
                    _mySession.MdwsConfiguration.AllConfigs.ContainsKey(MdwsConfigConstants.SM_CONFIG_SECTION) &&
                    _mySession.MdwsConfiguration.AllConfigs[MdwsConfigConstants.SM_CONFIG_SECTION].ContainsKey(MdwsConfigConstants.SM_CONNECTION_STRING) &&
                    !String.IsNullOrEmpty(_mySession.MdwsConfiguration.AllConfigs[MdwsConfigConstants.SM_CONFIG_SECTION][MdwsConfigConstants.SM_CONNECTION_STRING]))
                {
                    return pwd = _mySession.MdwsConfiguration.AllConfigs[MdwsConfigConstants.SM_CONFIG_SECTION][MdwsConfigConstants.SM_CONNECTION_STRING];
                }

            }
            return null;
        }

        public MessageTO sendDraft(string pwd, Int32 messageId, Int32 messageOplock)
        {
            MessageTO result = new MessageTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (messageId <= 0)
            {
                result.fault = new FaultTO("Must supply a valid message ID");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    SecureMessageDao dao = new SecureMessageDao(cxn);
                    gov.va.medora.mdo.domain.sm.Message msg = new Message() { Id = messageId, Oplock = messageOplock };
                    result = new MessageTO(dao.sendDraft(msg));
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public ThreadTO saveDraft(string pwd, Int32 replyingToMessageId, string threadSubject, Int32 messageCategory, 
            Int32 messageId, Int32 senderId, Int32 recipientId, string messageBody, Int32 messageOplock, Int32 threadOplock)
        {
            ThreadTO result = new ThreadTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (messageCategory > 0 && !Enum.IsDefined(typeof(gov.va.medora.mdo.domain.sm.enums.MessageCategoryTypeEnum), messageCategory))
            {
                result.fault = new FaultTO("Invalid message category");
            }
            else if (String.IsNullOrEmpty(messageBody))
            {
                result.fault = new FaultTO("Missing message body");
            }
            else if (messageId > 0 && messageOplock < 0)
            {
                result.fault = new FaultTO("Invalid message ID/message oplock");
            }
            else if (senderId <= 0 || recipientId <= 0)
            {
                result.fault = new FaultTO("Invalid sender/recipient");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Message message = new Message()
                {
                    Body = messageBody,
                    Checksum = StringUtils.getMD5Hash(messageBody),
                    Id = messageId,
                    MessageThread = new Thread(),
                    RecipientId = recipientId,
                    SenderId = senderId,
                    Oplock = messageOplock
                };
                message.MessageThread.Subject = threadSubject;
                if (Enum.IsDefined(typeof(mdo.domain.sm.enums.MessageCategoryTypeEnum), messageCategory))
                {
                    message.MessageThread.MessageCategoryType = (mdo.domain.sm.enums.MessageCategoryTypeEnum)messageCategory;
                }
                message.MessageThread.Oplock = threadOplock;
                
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    SecureMessageDao dao = new SecureMessageDao(cxn);
                    
                    if (replyingToMessageId > 0)
                    {
                        Message replyingToMsg = dao.getMessage(replyingToMessageId);
                        if (replyingToMsg == null || replyingToMsg.Id <= 0 || replyingToMsg.MessageThread == null || replyingToMsg.MessageThread.Id <= 0)
                        {
                            throw new Exception("Invalid reply to message ID");
                        }
                        message.MessageThread.Id = replyingToMsg.MessageThread.Id;
                    }

                    gov.va.medora.mdo.domain.sm.Message savedDraft = dao.saveDraft(message);
                    MessageTO msg = new MessageTO(savedDraft);
                    result = new ThreadTO(savedDraft.MessageThread);
                    result.messages = new MessageTO[] { msg };
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public MessageTO deleteDraft(string pwd, Int32 messageId)
        {
            MessageTO result = new MessageTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (messageId <= 0)
            {
                result.fault = new FaultTO("Missing message ID");
            }
            
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Message message = new Message() { Id = messageId };
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    SecureMessageDao dao = new SecureMessageDao(cxn);
                    dao.deleteDraft(message);
                }
                message.Addressees = null;
                message.MessageThread = null;
                message.Body = "OK";
                message.Id = -1;
                result = new MessageTO(message);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public MessageTO sendReplyMessage(string pwd, Int32 replyingToMessageId, Int32 senderId, Int32 recipientId, string messageBody)
        {
            MessageTO result = new MessageTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (replyingToMessageId <= 0)
            {
                result.fault = new FaultTO("Missing reply message ID");
            }
            else if (senderId <= 0)
            {
                result.fault = new FaultTO("Missing sender ID");
            }
            //else if (recipientId <= 0)
            //{
            //    result.fault = new FaultTO("Missing recipient ID");
            //}
            else if (String.IsNullOrEmpty(messageBody))
            {
                result.fault = new FaultTO("Must supply a message body");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    SecureMessageDao dao = new SecureMessageDao(cxn);
                    Message replyingTo = dao.getMessage(replyingToMessageId);
                    if (replyingTo == null || replyingTo.Id <= 0)
                    {
                        throw new Exception("No message found for that ID");
                    }

                    Message newReply = new Message() 
                    { 
                        SentDate = DateTime.Now,
                        SenderId = senderId, 
                        RecipientId = recipientId, 
                        Body = messageBody, 
                        Checksum = StringUtils.getMD5Hash(messageBody),
                        MessageThread = replyingTo.MessageThread
                    };

                    result = new MessageTO(dao.sendReply(replyingTo, newReply));
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public ThreadTO sendNewMessage(string pwd, string threadSubject, Int32 groupId, Int32 messageCategory, Int32 senderId, Int32 recipientId, string messageBody)
        {
            ThreadTO result = new ThreadTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (String.IsNullOrEmpty(threadSubject))
            {
                result.fault = new FaultTO("Missing thread subject");
            }
            else if (messageCategory >= 0 && !Enum.IsDefined(typeof(gov.va.medora.mdo.domain.sm.enums.MessageCategoryTypeEnum), messageCategory))
            {
                result.fault = new FaultTO("That message category is not defined");
            }
            else if (senderId <= 0)
            {
                result.fault = new FaultTO("Missing sender ID");
            }
            else if (recipientId <= 0)
            {
                result.fault = new FaultTO("Missing recipient ID");
            }
            else if (String.IsNullOrEmpty(messageBody))
            {
                result.fault = new FaultTO("Must supply a message body");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                gov.va.medora.mdo.domain.sm.Thread thread = new Thread()
                {
                    MailGroup = new TriageGroup() { Id = groupId },
                    MessageCategoryType = (mdo.domain.sm.enums.MessageCategoryTypeEnum)messageCategory,
                    Subject = threadSubject
                };
                gov.va.medora.mdo.domain.sm.Message message = new Message()
                {
                    Body = messageBody,
                    Checksum = StringUtils.getMD5Hash(messageBody),
                    MessageThread = thread,
                    RecipientId = recipientId,
                    SenderId = senderId,
                    SentDate = DateTime.Now
                };

                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    SecureMessageDao dao = new SecureMessageDao(cxn);
                    Message newMsg = dao.sendNewMessage(message);
                    result = new ThreadTO(newMsg.MessageThread);
                    result.messages = new MessageTO[] { new MessageTO(newMsg) };
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public ThreadTO updateMessageThread(string pwd, Int32 threadId, string threadSubject, Int32 messageCategory, Int32 threadOplock)
        {
            ThreadTO result = new ThreadTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (threadId <= 0)
            {
                result.fault = new FaultTO("Must specify a message thread");
            }
            else if (String.IsNullOrEmpty(threadSubject))
            {
                result.fault = new FaultTO("Missing thread subject");
            }
            else if (messageCategory > 0 && !Enum.IsDefined(typeof(gov.va.medora.mdo.domain.sm.enums.MessageCategoryTypeEnum), messageCategory))
            {
                result.fault = new FaultTO("That message category is not defined");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                gov.va.medora.mdo.domain.sm.Thread thread = new gov.va.medora.mdo.domain.sm.Thread()
                {
                    Id = threadId,
                    Subject = threadSubject,
                    MessageCategoryType = (gov.va.medora.mdo.domain.sm.enums.MessageCategoryTypeEnum)messageCategory,
                    Oplock = threadOplock
                };

                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    SecureMessageDao dao = new SecureMessageDao(cxn);
                    gov.va.medora.mdo.domain.sm.Thread dbThread = dao.getMessagesFromThread(threadId);

                    // we don't want to permit apps to change the mail group this way so just keep what's in the database which gets set through the proper channels
                    thread.MailGroup = dbThread.MailGroup;

                    if (dbThread == null || dbThread.Id <= 0 || dbThread.Messages == null || dbThread.Messages.Count <= 0)
                    {
                        throw new Exception("That thread does not exist in the database or appears malformed");
                    }

                    // make sure the thread hasn't been marked as completed
                    foreach(Message m in dbThread.Messages)
                    {
                        if (m.CompletedDate.Year > 1900)
                        {
                            throw new Exception("That message thread has already been completed. Unable to edit.");
                        }
                    }

                    result = new ThreadTO(dao.updateThread(thread));
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }


        public SmUserTO getUser(string pwd, string userId, string idType)
        {
            SmUserTO result = new SmUserTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (String.IsNullOrEmpty(userId))
            {
                result.fault = new dto.FaultTO("Must supply user ID");
            }
            else if (String.IsNullOrEmpty(idType) && !StringUtils.isNumeric(userId))
            {
                result.fault = new FaultTO("Invalid user ID");
            }
            else if (!String.IsNullOrEmpty(idType) && !String.Equals(idType, "SMID", StringComparison.CurrentCultureIgnoreCase) &&
                !String.Equals(idType, "ICN", StringComparison.CurrentCultureIgnoreCase) && !String.Equals(idType, "SSN", StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO("Invalid id type", "Use one of the following: SMID, ICN, SSN");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    UserDao dao = new UserDao(cxn);

                    if (String.IsNullOrEmpty(idType) || String.Equals(idType, "SMID", StringComparison.CurrentCultureIgnoreCase))
                    {
                        result = new SmUserTO(dao.getUserDetail(Convert.ToInt32(userId)));
                    }
                    else if (String.Equals(idType, "ICN", StringComparison.CurrentCultureIgnoreCase))
                    {
                        result = new SmUserTO(dao.getUserDetail(dao.getUserByIcn(userId).Id));
                    }
                    else if (String.Equals(idType, "SSN", StringComparison.CurrentCultureIgnoreCase))
                    {
                        // TODO - write get user by SSN function
                        result.fault = new FaultTO("SSN lookup not currently enabled. Please try again later");
                        //result = new SmUserTO(dao.getUserDetail(dao.getUserBySsn(userId)));
                    }
                    else
                    {
                        throw new Exception("Invalid user type"); // should never get here with fault handling section but... just in case
                    }
                }
            }
            catch (Exception exc)
            {
                result.fault = new dto.FaultTO(exc);
            }

            return result;
        }

        public dto.sm.AttachmentTO getAttachment(string pwd, Int32 attachmentId)
        {
            AttachmentTO result = new AttachmentTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (attachmentId <= 0)
            {
                result.fault = new FaultTO("Invalid attachment ID");
            }

            if (result.fault != null)
            {
                return result;
            }
            try
            {
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    AttachmentDao dao = new AttachmentDao(cxn);
                    MessageAttachment attachment = dao.getAttachment(attachmentId);
                    result = new AttachmentTO(attachment);
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public dto.sm.AttachmentTO addAttachment(string pwd, Int32 messageId, Int32 messageOplock, string fileName, string mimeType, AttachmentTO attachment)
        {
            AttachmentTO result = new AttachmentTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (messageId <= 0)
            {
                result.fault = new FaultTO("Invalid message ID");
            }
            else if (String.IsNullOrEmpty(fileName) || String.IsNullOrEmpty(mimeType) || attachment == null || attachment.attachment == null || attachment.attachment.Length <= 0)
            {
                result.fault = new FaultTO("Must supply all attachment properties");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    AttachmentDao dao = new AttachmentDao(cxn);
                    MessageAttachment newAttachment = dao.attachToMessage(
                        new MessageAttachment() { AttachmentName = fileName, MimeType = mimeType, SmFile = attachment.attachment },
                        new Message() { Id = messageId, Oplock = messageOplock });
                    result = new AttachmentTO(newAttachment);
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public dto.sm.AttachmentTO updateAttachment(string pwd, Int32 attachmentId, Int32 attachmentOplock, string fileName, string mimeType, AttachmentTO newAttachment)
        {
            AttachmentTO result = new AttachmentTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (attachmentId <= 0)
            {
                result.fault = new FaultTO("Invalid attachment ID");
            }
            else if (String.IsNullOrEmpty(fileName) || String.IsNullOrEmpty(mimeType) || newAttachment == null || newAttachment.attachment == null || newAttachment.attachment.Length <= 0)
            {
                result.fault = new FaultTO("Must supply all attachment properties");
            }

            if (result.fault != null)
            {
                return result;
            }
            try
            {
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    AttachmentDao dao = new AttachmentDao(cxn);
                    MessageAttachment updatedAttachment = dao.updateAttachment(new MessageAttachment() 
                        { Id = attachmentId, Oplock = attachmentOplock, AttachmentName = fileName, MimeType = mimeType, SmFile = newAttachment.attachment });
                    updatedAttachment.SmFile = null; // don't pass back - client already has image so save the bandwidth
                    result = new AttachmentTO(updatedAttachment);
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public dto.sm.MessageTO deleteAttachment(string pwd, Int32 messageId)
        {
            MessageTO result = new MessageTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (messageId <= 0)
            {
                result.fault = new FaultTO("Invalid message ID");
            }

            if (result.fault != null)
            {
                return result;
            }
            try
            {
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    AttachmentDao dao = new AttachmentDao(cxn);
                    Message dbMsg = dao.deleteAttachmentFromMessage(messageId);
                    result = new MessageTO(dbMsg);
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public dto.sm.MessageTO readMessage(string pwd, Int32 addresseeId, Int32 addresseeOplock)
        {
            MessageTO result = new MessageTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }
            else if (addresseeId <= 0)
            {
                result.fault = new FaultTO("Must supply addressee ID");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    AddresseeDao dao = new AddresseeDao(cxn);
                    gov.va.medora.mdo.domain.sm.Addressee addressee = dao.readMessage(new Addressee() { Id = addresseeId, Oplock = addresseeOplock });
                    MessageTO message = new MessageTO(addressee.Message);
                    message.addressees = new AddresseeTO[1] { new AddresseeTO(addressee) };
                    result = message;
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public MessageTO moveMessage(string pwd, Int32 userId, Int32 messageId, Int32 newFolderId)
        {
            MessageTO result = new MessageTO();

            pwd = getConnectionString(pwd);

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("No connection string specified or configured");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Message message = new Message();
                message.Id = messageId;
                message.Addressees = new List<Addressee>() { new Addressee() { FolderId = newFolderId, Owner = new User() { Id = userId } } };
                using (MdoOracleConnection cxn = new MdoOracleConnection(new mdo.DataSource() { ConnectionString = pwd }))
                {
                    AddresseeDao dao = new AddresseeDao(cxn);
                    message.Addressees[0] = dao.moveMessage(new Message() { Id = messageId }, new User() { Id = userId }, new Folder() { Id = newFolderId });
                    result = new MessageTO(message);
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }
    }
}