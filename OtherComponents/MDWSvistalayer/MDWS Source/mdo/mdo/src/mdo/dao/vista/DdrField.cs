using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class DdrField
    {
        String fmNumber;
        bool fExternal;
        String val;
        String externalVal;

        public DdrField() { }

        public DdrField(String fmNumber, bool fExternal)
        {
            FmNumber = fmNumber;
            HasExternal = fExternal;
        }

        public String FmNumber
        {
            get { return fmNumber; }
            set { fmNumber = value; }
        }

        public bool HasExternal
        {
            get {return fExternal;}
            set {fExternal = value;}
        }

        public String Value
        {
            get { return val; }
            set { val = value; }
        }

        public String ExternalValue
        {
            get { return externalVal; }
            set { externalVal = value; }
        }

    }
}
