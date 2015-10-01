using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class TimeSlotTO : AbstractTO
    {
        public DateTime start;
        public DateTime end;
        public string text;
        public bool available;

        public TimeSlotTO() { }

        public TimeSlotTO(mdo.TimeSlot timeSlot)
        {
            if (timeSlot == null)
            {
                return;
            }
            this.available = timeSlot.Available;
            this.end = timeSlot.End;
            this.start = timeSlot.Start;
            this.text = timeSlot.Text;
        }

    }
}