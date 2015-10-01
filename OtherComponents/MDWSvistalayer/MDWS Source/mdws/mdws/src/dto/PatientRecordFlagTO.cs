using System;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PatientRecordFlagTO : AbstractTO
    {
        public string id;
        public string name;
        public string actionId;
        public string actionName;
        public string actionTimestamp;
        public string noteId;

        public PatientRecordFlagTO() { }

        public PatientRecordFlagTO(PatientRecordFlag mdo)
        {
            this.id = mdo.Id;
            this.name = mdo.Name;
            this.actionId = mdo.ActionId;
            this.actionName = mdo.ActionName;
            this.actionTimestamp = mdo.ActionTimestamp;
            this.noteId = mdo.NoteId;
        }
    }
}
