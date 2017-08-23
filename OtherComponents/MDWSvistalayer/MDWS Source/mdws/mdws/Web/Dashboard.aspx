<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Dashboard.aspx.cs" Inherits="gov.va.medora.mdws.Web.Dashboard" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
<head runat="server">
    <title>MDWS Dashboard</title>
	<link rel="stylesheet" href="css/jquery.treeview.css" />
	<link rel="stylesheet" href="css/screen.css" />
	
	<script src="js/jquery.js" type="text/javascript"></script>
	<script src="js/jquery.treeview.js" type="text/javascript"></script>
    <script type="text/javascript" language="javascript">
        $(document).ready(function(){
            $("#browser").treeview({
		        persist: "location",
		        collapsed: true,
		        unique: true
	        });
	    });
    </script>
</head>
<body>
    <form id="form1" runat="server">
    <div>
        <img src="images/mdws_dashboard.png" alt="" width="581" height="54" />
    </div>
    <br /><br />
    <asp:Label ID="labelMessage" Font-Bold="true" ForeColor="Red" runat="server" /><br />
    <asp:Panel ID="panelDashboard" runat="server" Visible="false">
        <strong>Your MDWS Vitals:</strong>
        <br /><br />
        Up-Time: <asp:Label runat="server" ID="labelUpTime" />
        <br />
        Current MDWS Sessions (<asp:Label runat="server" ID="labelSessionCount" />):
        <br /><br />
        <div>
            <asp:Repeater runat="server" ID="repeaterSession">
                <HeaderTemplate>
                    <ul id="browser" class="filetree">
                </HeaderTemplate>
            
                <ItemTemplate>
                        <li><span><strong><%# DataBinder.Eval(Container.DataItem, "RequestingIP")%></strong></span>
                            <ul>
                                <li><span>
                                    Session Duration: <%# getDuration(DataBinder.Eval(Container.DataItem, "Start")) %>
                                </span></li>
                                <li>
                                    <span><%# DataBinder.Eval(Container.DataItem, "Requests.Count") %> Requests:</span>
                                    <ul>
                                            <asp:Repeater runat="server" ID="repeaterRequests" 
                                                DataSource='<%# ((gov.va.medora.mdws.ApplicationSession)Container.DataItem).Requests  %>' >
                                                <ItemTemplate>
                                                    <li>
                                                        <span><%# DataBinder.Eval(Container.DataItem, "Uri") %><br /></span>
    <%--                                                    <ul>
                                                            <li>
                                                                <strong>Request Body:<br /></strong>
                                                                <%# ((gov.va.medora.mdws.ApplicationRequest)Container.DataItem).RequestBody %>
                                                            </li>
                                                            <li>
                                                                <strong>Response Body:<br /></strong>
                                                                <%# ((gov.va.medora.mdws.ApplicationRequest)Container.DataItem).ResponseBody %>
                                                            </li>
                                                        </ul>
    --%>                                                </li>
                                                </ItemTemplate>
                                            </asp:Repeater>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                </ItemTemplate>
            
                <FooterTemplate>
                    </ul>
                </FooterTemplate>
            </asp:Repeater>
        </div>
    </asp:Panel>
    </form>
</body>
</html>
