using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.oracle.mhv.sm
{
    public static class SmUtils
    {
        public static String buildDetailString(domain.sm.Message message)
        {
            StringBuilder sb = new StringBuilder();
            sb.Append("MOBILE_APPS_ENTRY^Recipient:");
            sb.Append(message.RecipientId.ToString());
            if (message.MessageThread != null && message.MessageThread.MailGroup != null)
            {
                sb.Append("^");
                sb.Append(message.MessageThread.MailGroup.Id);
                sb.Append("^");
                sb.Append(message.MessageThread.MailGroup.Name);
            }
            return sb.ToString();
        }
    }
}
