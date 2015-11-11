using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm.enums
{
    [Serializable]
	public enum ParticipantTypeEnum
	{
        PATIENT = 1,
        CLINICIAN = 2,
        CLINCIAN_TRIAGE = 3,
        //ADMIN,
        DISTRIBUTION_GROUP = 4
	}
}
