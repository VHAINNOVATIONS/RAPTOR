using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class CytologyReport : PathologyReport
    {
        string supplementalRpt;

        public CytologyReport() { }

        public string SupplementalReport
        {
            get { return supplementalRpt; }
            set { supplementalRpt = value; }
        }

    }
}
