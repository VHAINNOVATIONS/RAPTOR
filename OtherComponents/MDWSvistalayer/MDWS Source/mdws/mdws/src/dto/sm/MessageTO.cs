using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.sm
{
    [Serializable]
    public class MessageTO : BaseSmTO
    {
        public Int32 threadId;
        public Int32 threadOplock;
        public string body;
        //public ClinicianStatusEnum status;
        public DateTime completedDate;
        public SmClinicianTO statusSetBy;
        public SmClinicianTO assignedTo;
        public string checksum;
        public DateTime sentDate;
        public DateTime sentDateLocal;
        public DateTime escalatedDate;
        public DateTime escalationNotificationDate;
        public Int32 escalationNotificationTries;
        //public ParticipantTypeEnum senderType;
        public Int32 senderId;
        public string senderName;
        //public ParticipantTypeEnum recipientType;
        public Int32 recipientId;
        public string recipientName;
        public Int32 ccRecipientId;
        public string ccRecipientName;
        public string readReceipt;
        public bool attachment;
        public Int32 attachmentId;
        public AddresseeTO[] addressees;  

        public MessageTO() { }

        public MessageTO(mdo.domain.sm.Message message) 
        {
            if (message == null)
            {
                return;
            }

            this.id = message.Id;
            this.oplock = message.Oplock;
            this.assignedTo = new SmClinicianTO(message.AssignedTo);
            this.attachment = message.Attachment;
            this.attachmentId = Convert.ToInt32(message.AttachmentId);
            this.body = gov.va.medora.utils.StringUtils.stripInvalidXmlCharacters(message.Body); // quickly found some invalid XML characters
            this.ccRecipientId = Convert.ToInt32(message.CcRecipientId);
            this.ccRecipientName = message.CcRecipientName;
            this.checksum = gov.va.medora.utils.StringUtils.stripInvalidXmlCharacters(message.Checksum);
            this.completedDate = message.CompletedDate;
            this.escalatedDate = message.EscalatedDate;
            this.escalationNotificationDate = message.EscalationNotificationDate;
            this.escalationNotificationTries = Convert.ToInt32(message.EscalationNotificationTries);
            this.readReceipt = message.ReadReceipt;
            this.recipientId = Convert.ToInt32(message.RecipientId);
            this.recipientName = message.RecipientName;
            //this.recipientType = message.RecipientType;
            this.senderId = Convert.ToInt32(message.SenderId);
            this.senderName = message.SenderName;
            //this.senderType = message.SenderType;
            this.sentDate = message.SentDate;
            this.sentDateLocal = message.SentDateLocal;
            //this.status = message.Status;
            this.statusSetBy = new SmClinicianTO(message.StatusSetBy);

            if (message.MessageThread != null)
            {
                threadId = message.MessageThread.Id;
                threadOplock = message.MessageThread.Oplock;
            }
            if (message.Addressees != null && message.Addressees.Count > 0)
            {
                addressees = new AddresseeTO[message.Addressees.Count];
                for (int i = 0; i < message.Addressees.Count; i++)
                {
                    addressees[i] = new AddresseeTO(message.Addressees[i]);
                }
            }
        }
    }
}