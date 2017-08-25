using System;
using gov.va.medora.mdo;

/// <summary>
/// Summary description for ObservationTO
/// </summary>

namespace gov.va.medora.mdws.dto
{
    public class LabObservationTO : AbstractTO
    {
        public LabObservationTypeTO observationType;
        public String value;
        public String value1;
        public String value2;
        public String method;
        public String qualifier;
        public String standardized;
        public String device;
        public String status;
        public String timestamp;

        public LabObservationTO() { }

        public LabObservationTO(LabObservation mdoObj)
        {
            if (mdoObj == null)
            {
                return;
            }
            if (mdoObj.Type != null)
            {
                this.observationType = new LabObservationTypeTO(mdoObj.Type);
            }
            if (mdoObj.Value != "")
            {
                this.value = mdoObj.Value;
            }
            if (mdoObj.Value1 != "")
            {
                this.value1 = mdoObj.Value1;
            }
            if (mdoObj.Value2 != "")
            {
                this.value2 = mdoObj.Value2;
            }
            if (mdoObj.Method != "")
            {
                this.method = mdoObj.Method;
            }
            if (mdoObj.Qualifier != "")
            {
                this.qualifier = mdoObj.Qualifier;
            }
            if (mdoObj.Standardized != "")
            {
                this.standardized = mdoObj.Standardized;
            }
            if (mdoObj.Device != "")
            {
                this.device = mdoObj.Device;
            }
            if (mdoObj.Status != "")
            {
                this.status = mdoObj.Status;
            }
            if (mdoObj.Timestamp != null)
            {
                this.timestamp = mdoObj.Timestamp.ToString("yyyyMMdd.HHmmss");
            }
        }
    }
}