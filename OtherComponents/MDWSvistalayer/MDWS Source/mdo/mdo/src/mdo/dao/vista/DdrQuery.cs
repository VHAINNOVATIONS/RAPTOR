using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class DdrQuery
    {
        AbstractConnection cxn;

        public DdrQuery(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        public void setConnection(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        //public VistaRpcQuery executePlus(MdoQuery vq)
        //{
        //    if (cxn.Account.PrimaryPermission == null ||
        //        String.IsNullOrEmpty(cxn.Account.PrimaryPermission.Name))
        //    {
        //        throw new UnauthorizedAccessException("Current context is empty");
        //    }
        //    AbstractPermission currentContext = cxn.Account.PrimaryPermission;

        //    if (currentContext.Name != VistaConstants.MDWS_CONTEXT && currentContext.Name != VistaConstants.DDR_CONTEXT)
        //    {
        //        changeContext(cxn);
        //    }

        //    VistaRpcQuery result = ((VistaConnection)cxn).query(vq, null, true);

        //    if (currentContext.Name != VistaConstants.MDWS_CONTEXT && currentContext.Name != VistaConstants.DDR_CONTEXT)
        //    {
        //        ((VistaAccount)cxn.Account).setContext(currentContext);
        //    }

        //    return result;
        //}

        public string execute(MdoQuery vq)
        {
            if (cxn.Account.PrimaryPermission == null ||
                String.IsNullOrEmpty(cxn.Account.PrimaryPermission.Name))
            {
                throw new UnauthorizedAccessException("Current context is empty");
            }
            AbstractPermission currentContext = cxn.Account.PrimaryPermission;

            if (currentContext.Name != VistaConstants.MDWS_CONTEXT && currentContext.Name != VistaConstants.DDR_CONTEXT)
            {
                changeContext(cxn);
            }

            string response = (string)cxn.query(vq);

            if (currentContext.Name != VistaConstants.MDWS_CONTEXT && currentContext.Name != VistaConstants.DDR_CONTEXT)
            {
                ((VistaAccount)cxn.Account).setContext(currentContext);
            }
            return response;
        }

        internal void changeContext(AbstractConnection cxn)
        {
            VistaAccount acct = (VistaAccount)cxn.Account;

            //if (acct.Permissions.ContainsKey(VistaConstants.MDWS_CONTEXT))
            //{
            //    acct.setContext(acct.Permissions[VistaConstants.MDWS_CONTEXT]);
            //}
            //else
            //{
            //    acct.addContextInVista(cxn.Uid, new MenuOption(VistaConstants.MDWS_CONTEXT));
            //}
            if (acct.hasPermission(acct.Permissions, new MenuOption(VistaConstants.DDR_CONTEXT)))
            {
                acct.setContext(acct.Permissions[VistaConstants.DDR_CONTEXT]);
            }
            else
            {
                acct.addContextInVista(cxn.Uid, new MenuOption(VistaConstants.DDR_CONTEXT));
                //acct.setContext(new MenuOption(VistaConstants.DDR_CONTEXT));
            }
        }
    }
}
