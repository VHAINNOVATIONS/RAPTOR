using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class DischargeDiagnosesTO : AbstractTO
    {
        string admitTimestamp;
        string dischargeTimestamp;
        DiagnosisTO primaryDx;
        DiagnosisArray secondaryDxs;

        public DischargeDiagnosesTO() { }

        public DischargeDiagnosesTO(DischargeDiagnoses mdo)
        {
            this.admitTimestamp = mdo.AdmitTimestamp;
            this.dischargeTimestamp = mdo.DischargeTimestamp;
            this.primaryDx = new DiagnosisTO(mdo.PrimaryDx);
            this.secondaryDxs = new DiagnosisArray(mdo.SecondaryDxs);
        }
    }
}
