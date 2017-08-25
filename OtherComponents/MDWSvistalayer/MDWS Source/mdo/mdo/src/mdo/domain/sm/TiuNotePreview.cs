using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class TiuNotePreview
    {
        private bool _isLocked;

        public bool IsLocked
        {
            get { return _isLocked; }
            set { _isLocked = value; }
        }
        private gov.va.medora.mdo.domain.sm.Patient _patient;

        public gov.va.medora.mdo.domain.sm.Patient Patient
        {
            get { return _patient; }
            set { _patient = value; }
        }
        private DateTime _createdDate;

        public DateTime CreatedDate
        {
            get { return _createdDate; }
            set { _createdDate = value; }
        }
        private string _facilityName;

        public string FacilityName
        {
            get { return _facilityName; }
            set { _facilityName = value; }
        }
        private bool _isAddendum;

        public bool IsAddendum
        {
            get { return _isAddendum; }
            set { _isAddendum = value; }
        }
        private string _existingNote;

        public string ExistingNote
        {
            get { return _existingNote; }
            set { _existingNote = value; }
        }
        private string _proposedNote;

        public string ProposedNote
        {
            get { return _proposedNote; }
            set { _proposedNote = value; }
        }
        private List<Message> _existingMessages = new List<Message>();

        public List<Message> ExistingMessages
        {
            get { return _existingMessages; }
            set { _existingMessages = value; }
        }
        private List<Message> _proposedMessages = new List<Message>();

        public List<Message> ProposedMessages
        {
            get { return _proposedMessages; }
            set { _proposedMessages = value; }
        }
    }
}
