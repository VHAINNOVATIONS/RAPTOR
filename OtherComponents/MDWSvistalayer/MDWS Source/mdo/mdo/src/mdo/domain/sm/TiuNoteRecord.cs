using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class TiuNoteRecord : BaseModel
    {
        private string _vistaDiv;

        public string VistaDiv
        {
            get { return _vistaDiv; }
            set { _vistaDiv = value; }
        }
        private Int64 _threadId;

        public Int64 ThreadId
        {
            get { return _threadId; }
            set { _threadId = value; }
        }
        private Int64 _lastMessageId;

        public Int64 LastMessageId
        {
            get { return _lastMessageId; }
            set { _lastMessageId = value; }
        }
        private DateTime _lockedDate;

        public DateTime LockedDate
        {
            get { return _lockedDate; }
            set { _lockedDate = value; }
        }
        private string _conversationId;

        public string ConversationId
        {
            get { return _conversationId; }
            set { _conversationId = value; }
        }
        private DateTime _noteCreationDate;

        public DateTime NoteCreationDate
        {
            get { return _noteCreationDate; }
            set { _noteCreationDate = value; }
        }
        private string _comments;

        public string Comments
        {
            get { return _comments; }
            set { _comments = value; }
        }
    }
}
