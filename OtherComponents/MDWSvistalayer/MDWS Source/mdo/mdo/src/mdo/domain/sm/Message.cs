using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm.enums;

namespace gov.va.medora.mdo.domain.sm
{
    public class Message : PersistentObject, IComparable
    {
        private Thread _thread;
        private string _body;
        private ClinicianStatusEnum _status;
        private DateTime _completedDate;
        private Clinician _statusSetBy;
        private Clinician _assignedTo;
        private string _checksum;
        private DateTime _sentDate;
        private DateTime _sentDateLocal;
        private DateTime _escalatedDate;
        private DateTime _escalationNotificationDate;
        private long _escalationNotificationTries;
        private ParticipantTypeEnum _senderType;
        private long _senderId;
        private string _senderName;
        private ParticipantTypeEnum _recipientType;
        private long _recipientId;
        private string _recipientName;
        private long _ccRecipientId;
        private string _ccRecipientName;
        private string _readReceipt;
        private bool _attachment;
        private long _attachmentId;
        private List<Addressee> _addressees;

        public Message()
        {
            _status = ClinicianStatusEnum.INCOMPLETE;
        }

        public Clinician AssignedTo
        {
            get { return _assignedTo; }
            set { _assignedTo = value; }
        }

        public string Body
        {
            get { return _body; }
            set { _body = value; }
        }

        public ClinicianStatusEnum Status
        {
            get { return _status; }
            set { _status = value; }
        }

        public Clinician StatusSetBy
        {
            get { return _statusSetBy; }
            set { _statusSetBy = value; }
        }

        public Thread MessageThread
        {
            get { return _thread; }
            set { _thread = value; }
        }

        public DateTime CompletedDate
        {
            get { return _completedDate; }
            set { _completedDate = value; }
        }

        public List<Addressee> Addressees
        {
            get { return _addressees; }
            set { _addressees = value; }
        }

        public string Checksum
        {
            get { return _checksum; }
            set { _checksum = value; }
        }

        public DateTime SentDate
        {
            get { return _sentDate; }
            set { _sentDate = value; }
        }

        public DateTime SentDateLocal
        {
            get { return _sentDateLocal; }
            set { _sentDateLocal = value; }
        }

        public DateTime EscalatedDate
        {
            get { return _escalatedDate; }
            set { _escalatedDate = value; }
        }

        public bool isEscalated()
        {
            return _escalatedDate != null || DateTime.Compare(new DateTime(), _escalatedDate) == 0;
        }

        public DateTime EscalationNotificationDate
        {
            get { return _escalationNotificationDate; }
            set { _escalationNotificationDate = value; }
        }

        public long EscalationNotificationTries
        {
            get { return _escalationNotificationTries; }
            set { _escalationNotificationTries = value; }
        }

        public ParticipantTypeEnum SenderType
        {
            get { return _senderType; }
            set { _senderType = value; }
        }

        public long SenderId
        {
            get { return _senderId; }
            set { _senderId = value; }
        }

        public string SenderName
        {
            get { return _senderName; }
            set { _senderName = value; }
        }

        public ParticipantTypeEnum RecipientType
        {
            get { return _recipientType; }
            set { _recipientType = value; }
        }

        public long RecipientId
        {
            get { return _recipientId; }
            set { _recipientId = value; }
        }

        public string RecipientName
        {
            get { return _recipientName; }
            set { _recipientName = value; }
        }

        public string ReadReceipt
        {
            get { return _readReceipt; }
            set { _readReceipt = value; }
        }

        public bool Attachment
        {
            get { return _attachment; }
            set { _attachment = value; }
        }

        public long CcRecipientId
        {
            get { return _ccRecipientId; }
            set { _ccRecipientId = value; }
        }

        public string CcRecipientName
        {
            get { return _ccRecipientName; }
            set { _ccRecipientName = value; }
        }

        public long AttachmentId
        {
            get { return _attachmentId; }
            set { _attachmentId = value; }
        }

        /// <summary>
        /// Compare two Message objects based on their sent date
        /// </summary>
        /// <param name="obj"></param>
        /// <returns></returns>
        public int CompareTo(object obj)
        {
            if (!(obj is Message))
            {
                throw new ArgumentException("Invalid object for comparison");
            }
            return this.SentDate.CompareTo(((Message)obj).SentDate);
        }

        internal static Message getMessageFromReader(System.Data.IDataReader rdr)
        {
            return getMessageFromReader(rdr, mdo.dao.oracle.mhv.sm.QueryUtils.getColumnExistsTable(gov.va.medora.mdo.dao.oracle.mhv.sm.TableSchemas.SECURE_MESSAGE_COLUMNS, rdr));
        }

        internal static Message getMessageFromReader(System.Data.IDataReader rdr, Dictionary<string, bool> columnTable)
        {
            Message msg = new Message();

            if (columnTable["SECURE_MESSAGE_ID"])
            {
                msg.Id = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("SECURE_MESSAGE_ID")));
            }
            if (columnTable["CLINICIAN_STATUS"])
            {
                msg.Status = (domain.sm.enums.ClinicianStatusEnum)Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("CLINICIAN_STATUS")));
            }
            if (columnTable["COMPLETED_DATE"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("COMPLETED_DATE")))
                {
                    msg.CompletedDate = rdr.GetDateTime(rdr.GetOrdinal("COMPLETED_DATE"));
                }
            }
            if (columnTable["ASSIGNED_TO"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("ASSIGNED_TO")))
                {
                    if (msg.AssignedTo == null)
                    {
                        msg.AssignedTo = new Clinician();
                    }
                    msg.AssignedTo.Id = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("ASSIGNED_TO")));
                }
            }
            if (columnTable["CHECKSUM"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("CHECKSUM")))
                {
                    msg.Checksum = rdr.GetString(rdr.GetOrdinal("CHECKSUM"));
                }
            }
            if (columnTable["THREAD_ID"])
            {
                if (msg.MessageThread == null)
                {
                    msg.MessageThread = new Thread();
                }
                msg.MessageThread.Id = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("THREAD_ID")));
            }
            if (columnTable["STATUS_SET_BY"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("STATUS_SET_BY")))
                {
                    if (msg.StatusSetBy == null)
                    {
                        msg.StatusSetBy = new Clinician();
                    }
                    msg.StatusSetBy.Id = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("STATUS_SET_BY")));
                }
            }
            if (columnTable["SMOPLOCK"])
            {
                msg.Oplock = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("SMOPLOCK")));
            }
            if (columnTable["ESCALATED"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("ESCALATED")))
                {
                    msg.EscalatedDate = rdr.GetDateTime(rdr.GetOrdinal("ESCALATED"));
                }
            }
            if (columnTable["BODY"])
            {
                msg.Body = rdr.GetString(rdr.GetOrdinal("BODY"));
            }
            if (columnTable["SENT_DATE"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("SENT_DATE")))
                {
                    msg.SentDate = rdr.GetDateTime(rdr.GetOrdinal("SENT_DATE"));
                }
            }
            if (columnTable["SENDER_TYPE"])
            {
                msg.SenderType = (domain.sm.enums.ParticipantTypeEnum)Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("SENDER_TYPE")));
            }
            if (columnTable["SENDER_ID"])
            {
                msg.SenderId = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("SENDER_ID")));
            }
            if (columnTable["SENDER_NAME"])
            {
                msg.SenderName = rdr.GetString(rdr.GetOrdinal("SENDER_NAME"));
            }
            if (columnTable["RECIPIENT_TYPE"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("RECIPIENT_TYPE")))
                {
                    msg.RecipientType = (domain.sm.enums.ParticipantTypeEnum)Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("RECIPIENT_TYPE")));
                }
            }
            if (columnTable["RECIPIENT_ID"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("RECIPIENT_ID")))
                {
                    msg.RecipientId = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("RECIPIENT_ID")));
                }
            }
            if (columnTable["RECIPIENT_NAME"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("RECIPIENT_NAME")))
                {
                    msg.RecipientName = rdr.GetString(rdr.GetOrdinal("RECIPIENT_NAME"));
                }
            }
            if (columnTable["SENT_DATE_LOCAL"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("SENT_DATE_LOCAL")))
                {
                    msg.SentDateLocal = rdr.GetDateTime(rdr.GetOrdinal("SENT_DATE_LOCAL"));
                }
            }
            if (columnTable["ESCALATION_NOTIFICATION_DATE"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("ESCALATION_NOTIFICATION_DATE")))
                {
                    msg.EscalationNotificationDate = rdr.GetDateTime(rdr.GetOrdinal("ESCALATION_NOTIFICATION_DATE"));
                }
            }
            if (columnTable["ESCALATION_NOTIFICATION_TRIES"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("ESCALATION_NOTIFICATION_TRIES")))
                {
                    msg.EscalationNotificationTries = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("ESCALATION_NOTIFICATION_TRIES")));
                }
            }
            if (columnTable["READ_RECEIPT"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("READ_RECEIPT")))
                {
                    msg.ReadReceipt = rdr.GetString(rdr.GetOrdinal("READ_RECEIPT"));
                }
            }
            if (columnTable["HAS_ATTACHMENT"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("HAS_ATTACHMENT")))
                {
                    msg.Attachment = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("HAS_ATTACHMENT"))) == 1;
                }
            }
            if (columnTable["ATTACHMENT_ID"])
            {
                if (!rdr.IsDBNull(rdr.GetOrdinal("ATTACHMENT_ID")))
                {
                    msg.AttachmentId = Convert.ToInt32(rdr.GetDecimal(rdr.GetOrdinal("ATTACHMENT_ID")));
                }
            }

            return msg;
        }

    }
}
