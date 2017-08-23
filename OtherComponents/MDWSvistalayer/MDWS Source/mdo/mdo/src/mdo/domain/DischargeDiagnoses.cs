using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class DischargeDiagnoses
    {
        string admitTimestamp;
        string dischargeTimestamp;
        Diagnosis primaryDx;
        Diagnosis[] secondaryDxs;

        public DischargeDiagnoses() { }

        public string AdmitTimestamp
        {
            get { return admitTimestamp; }
            set { admitTimestamp = value; }
        }

        public string DischargeTimestamp
        {
            get { return dischargeTimestamp; }
            set { dischargeTimestamp = value; }
        }

        public Diagnosis PrimaryDx
        {
            get { return primaryDx; }
            set { primaryDx = value; }
        }

        public Diagnosis[] SecondaryDxs
        {
            get { return secondaryDxs; }
            set { secondaryDxs = value; }
        }
    }
}
