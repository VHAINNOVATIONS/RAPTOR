using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class Facility
    {
        public long Id { get; set; }
        public string Name { get; set; }
        public string StationNumber { get; set; }
        public long ParentId { get; set; }
        public long VisnId { get; set; }
    }
}
