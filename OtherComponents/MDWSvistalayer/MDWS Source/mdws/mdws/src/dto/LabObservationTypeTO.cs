using System;
using gov.va.medora.mdo;

/// <summary>
/// Summary description for ObservationTypeTO
/// </summary>

namespace gov.va.medora.mdws.dto
{
    public class LabObservationTypeTO : AbstractTO
    {
        public String id = "";
        public String title = "";
        public String units = "";
        public String range = "";

        public LabObservationTypeTO() { }

        public LabObservationTypeTO(LabObservationType mdoObj)
        {
            if (mdoObj == null)
            {
                return;
            }
            if (mdoObj.Id != "")
            {
                this.id = mdoObj.Id;
            }
            if (mdoObj.Title != "")
            {
                this.title = mdoObj.Title;
            }
            if (mdoObj.Units != "")
            {
                this.units = mdoObj.Units;
            }
            if (mdoObj.Range != "")
            {
                this.range = mdoObj.Range;
            }
        }
    }
}