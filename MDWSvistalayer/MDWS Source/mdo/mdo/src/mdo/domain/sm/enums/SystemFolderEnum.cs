using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm.enums
{
    [Serializable]
    public enum SystemFolderEnum
    {
        Inbox = 0,
        Sent = -1,
        Drafts = -2,
        Deleted = -3,
        Completed = -4,
        Escalated = -5,
        Reminder = -6
    }
}
