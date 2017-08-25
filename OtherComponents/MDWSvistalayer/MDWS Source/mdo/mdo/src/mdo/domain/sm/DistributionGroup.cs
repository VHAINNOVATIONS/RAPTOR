using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm.enums;

namespace gov.va.medora.mdo.domain.sm
{
    public class DistributionGroup : BaseModel
    {
        public string Name { get; set; }
        public Clinician Owner { get; set; }
        public List<User> Members { get; set; }
        public bool PublicGroup { get; set; }
        public long VisnId { get; set; }

        public ParticipantTypeEnum getParticipantType()
        {
            return ParticipantTypeEnum.DISTRIBUTION_GROUP;
        }
    }
}
