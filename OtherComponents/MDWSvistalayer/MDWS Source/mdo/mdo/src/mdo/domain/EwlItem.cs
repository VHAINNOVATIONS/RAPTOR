using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class EwlItem
    {
        public string Id { get; set; }
        public string OriginatingDate { get; set; }
        public Site Institution { get; set; }
        public string Type { get; set; }
        public string Position { get; set; }
        public User EnteredBy { get; set; }
        public string Priority { get; set; }
        public string ScheduledDate { get; set; }
        public string AppointmentDate { get; set; }
        public HospitalLocation Clinic { get; set; }
        public string Status { get; set; }
        public string DispositionDate { get; set; }
        public string DispositionedBy { get; set; }
        public string RequestedAppointmentDate { get; set; }
        public string Comment { get; set; }

        public EwlItem() { }
    }
}
