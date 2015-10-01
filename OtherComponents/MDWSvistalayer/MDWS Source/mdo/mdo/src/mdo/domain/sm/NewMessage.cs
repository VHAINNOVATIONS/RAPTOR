using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class NewMessage
    {
        private MailParticipant _from;

        internal MailParticipant From
        {
            get { return _from; }
            set { _from = value; }
        }
        private MailParticipant _to;

        internal MailParticipant To
        {
            get { return _to; }
            set { _to = value; }
        }
        private MailParticipant _cc;

        internal MailParticipant Cc
        {
            get { return _cc; }
            set { _cc = value; }
        }
        private string _subject;

        public string Subject
        {
            get { return _subject; }
            set { _subject = value; }
        }
        private string _body;

        public string Body
        {
            get { return _body; }
            set { _body = value; }
        }
        private TriageGroup _triageGroup;

        public TriageGroup TriageGroup
        {
            get { return _triageGroup; }
            set { _triageGroup = value; }
        }
        private bool _draft;

        public bool Draft
        {
            get { return _draft; }
            set { _draft = value; }
        }
        private Int64 _attachmentId;

        public Int64 AttachmentId
        {
            get { return _attachmentId; }
            set { _attachmentId = value; }
        }
        private string _attachmentName;

        public string AttachmentName
        {
            get { return _attachmentName; }
            set { _attachmentName = value; }
        }
        private Int64 _messageCategoryTypeId;

        public Int64 MessageCategoryTypeId
        {
            get { return _messageCategoryTypeId; }
            set { _messageCategoryTypeId = value; }
        }
    }
}
