using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class PatientRecordFlag
    {
        string id;
        string name;
        string actionId;
        string actionName;
        string actionTimestamp;
        string noteId;

        public PatientRecordFlag() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string ActionId
        {
            get { return actionId; }
            set { actionId = value; }
        }

        public string ActionName
        {
            get { return actionName; }
            set { actionName = value; }
        }

        public string ActionTimestamp
        {
            get { return actionTimestamp; }
            set { actionTimestamp = value; }
        }

        public string NoteId
        {
            get { return noteId; }
            set { noteId = value; }
        }
    }
}
