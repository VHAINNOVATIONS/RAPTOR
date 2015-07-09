using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class MicrobiologyReport : LabReport
    {
        string sample;
        string text;

        public MicrobiologyReport() { }

        public string Sample
        {
            get { return sample; }
            set { sample = value; }
        }

        public string Text
        {
            get { return text; }
            set { text = value; }
        }

    }
}
