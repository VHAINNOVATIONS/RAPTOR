using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm.enums;

namespace gov.va.medora.mdo.domain.sm
{
    public class Administrator : gov.va.medora.mdo.domain.sm.User
    {
        // national administrator?
        public bool National { get; set; }

        // list of facilities that this administrator can manipulate
        public List<Facility> Facilities { get; set; }

        // list of VISNs that this administrator can manipulate
        private List<Facility> Visns { get; set; }

        public Administrator()
        {
            //super();
            //UserType = UserTypeEnum.ADMINISTRATOR;
            //ParticipantType = null;
        }

    }
}
