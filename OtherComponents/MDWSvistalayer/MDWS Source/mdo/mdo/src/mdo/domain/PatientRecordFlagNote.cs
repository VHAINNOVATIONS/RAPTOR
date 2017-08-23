using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class PatientRecordFlagNote
     {
        string noteIen;
        string actionName;
        string actionTimestamp;
        string doctorName;

        //10645578^NEW ASSIGNMENT^APR 08, 2011@14:14^DZIK,EILEEN
        public PatientRecordFlagNote() { }

        public string NoteIen
        {
            get { return noteIen; }
            set { noteIen = value; }
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

        public string DoctorName
        {
            get { return doctorName; }
            set { doctorName = value; }
        }
    }
}
