using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class SurgeryReport : TextReport
    {
        KeyValuePair<string,string> specialty;
        string status;
        string preOpDx;
        string postOpDx;
        string labWork;
        string dictationTimestamp;
        string transcriptionTimestamp;

        public SurgeryReport() { }

        public KeyValuePair<string, string> Specialty
        {
            get { return specialty; }
            set { specialty = value; }
        }

        public string Status
        {
            get { return status; }
            set { status = value; }
        }

        public string PreOpDx
        {
            get { return preOpDx; }
            set { preOpDx = value; }
        }

        public string PostOpDx
        {
            get { return postOpDx; }
            set { postOpDx = value; }
        }

        public string LabWork
        {
            get { return labWork; }
            set { labWork = value; }
        }

        public string DictationTimestamp
        {
            get { return dictationTimestamp; }
            set { dictationTimestamp = value; }
        }

        public string TranscriptionTimestamp
        {
            get { return transcriptionTimestamp; }
            set { transcriptionTimestamp = value; }
        }


    }
}
