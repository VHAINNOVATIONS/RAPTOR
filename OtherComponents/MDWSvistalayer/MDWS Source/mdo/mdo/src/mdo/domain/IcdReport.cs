using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class IcdReport : Report
    {
        string icdCode;

        public IcdReport() { }

        public string IcdCode
        {
            get { return icdCode; }
            set { icdCode = value; }
        }
    }
}
