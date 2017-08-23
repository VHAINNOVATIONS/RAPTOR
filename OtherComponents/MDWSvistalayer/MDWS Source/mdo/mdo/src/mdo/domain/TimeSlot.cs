using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class TimeSlot
    {
        public DateTime Start { get; set; }
        public DateTime End { get; set; }
        public string Text { get; set; }
        public bool Available { get; set; }

        public TimeSlot() { }

        public TimeSlot(DateTime start, DateTime end, bool available, string text)
        {
            this.Start = start;
            this.End = end;
            this.Available = available;
            this.Text = text;
        }
    }
}
