using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IVitalsDao
    {
        VitalSignSet[] getVitalSigns();
        VitalSignSet[] getVitalSigns(string pid);
        VitalSignSet[] getVitalSigns(string fromDate, string toDate, int maxRex);
        VitalSignSet[] getVitalSigns(string pid, string fromDate, string toDate, int maxRex);
        VitalSign[] getLatestVitalSigns();
        VitalSign[] getLatestVitalSigns(string pid);
    }
}
